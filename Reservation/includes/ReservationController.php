<?php   
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Owen Kellie-Smith
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @file
 */

/**
 *
 * ReservationController is used to input and process
 * requests about Reservation objects.
 *
 */
class ReservationController  {

	private $messages; 
	private $user;
	private $maxBookingDurationInHours = 168;
	private $maxBookingDeferralInDays = 60;
	private $blankTime = "";
	private $dateFormatUnixToLabel = "H:i D d-M-Y";
	private $dateFormatMySQL = "%H:%i %a %d-%b-%Y";

	public function __construct( $user = "A.N.Other"){
		$this->user = $user;
		$this->messages = array();
	}

	public function get_controller( $parameters) {
		$this->processPost( $parameters );
		$result['output']['unrendered']['table']['immediate'] = $this->getImmediateCapacityTable();
		$result['output']['unrendered']['table']['bookings'] = $this->getBookings();
		$result['output']['unrendered']['table']['log'] = $this->getLog();
	  	$result['output']['unrendered']['forms'][] = array(
			'content'=> $this->get_booking_form( NULL ),
			'type'=>'select',
			);
		$result['warning'] = $this->messages;
		return $result;
	}

	private function getUser(){
		return $this->user;
	}
		
	private function getBookings(){
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_booking_units',
	 		'res_beneficiary_name'=>'user_name',	
			'res_booking_startF'=>
				'DATE_FORMAT(res_booking_start, "' . $this->dateFormatMySQL . '")',
			'res_booking_endF'=>
				'DATE_FORMAT(res_booking_end, "' . $this->dateFormatMySQL . '")',
			'res_booking_id'
			);
		$bookings = $db->select(
			array('res_booking','res_resource','res_unit','user'), 
			$vars,
			array(
				'res_booking_beneficiary_id=user_id',
				'res_booking_resource_id=res_resource_id',
				'res_resource_unit_id=res_unit_id',
				'res_booking_end > now()',
				),
			__METHOD__,
			array( 'ORDER BY'=>array(
				'res_resource_name','res_booking_end',
				'res_booking_units','res_beneficiary_name'
				)
			)
			);
		$ret['data'] = $bookings;
		$ret['header'] = array(
			'Resource','Cores','For','Start','Stop','Cancel',
		);
		return $ret;
	}

	private function getLog(){
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_log_who',
			'res_log_whenF'=>
				'DATE_FORMAT(res_log_when, "' . $this->dateFormatMySQL . '")',
			'res_log_text',
			);
		$res = $db->select(
			array('res_log'), 
			$vars,
			array(),
			__METHOD__,
			array( 'LIMIT'=>100, 'ORDER BY'=>array('res_log_when DESC'))
			);
		$ret['data'] = $res;
		$ret['header'] = array(
			'Who','When','What',
		);
		return $ret;
	}

	private function getImmediateCapacityTable(){
		$result = array();
		$result['data'] = $this->getImmediateCapacity();
		$result['header'] = array(
			'Resource','Immediately available cores','Available for (hours), starting at ' . date("Y-m-d H:i", time() )  
		);
		return $result;
	}

	private function processPost( $p) {
		if( isset( $p['order'] )){
			if ( 'add_booking' == $p['order'] && 
				isset( $p['resource'] ) &&
				isset( $p['beneficiary'] ) &&
				isset( $p['quantity'] ) &&
				isset( $p['duration'] ) &&
				isset( $p['deferral'] ) 
			){
				$available = $this->get_available_capacity( 
					$p['resource'], $p['duration'], $p['deferral'] );
				if ($available  >= $p['quantity']) {
					$db = new ReservationDBInterface();
					$table="res_booking";
					$values=array(
					'res_booking_resource_id'=>$p['resource'],
					'res_booking_beneficiary_id'=>$p['beneficiary'],
					'res_booking_units'=>$p['quantity'],
					'res_booking_start'=>date("Y-m-d H:i:s", time() + $this->secondsFromHours( $p['deferral']) ),
					'res_booking_end'=>date("Y-m-d H:i:s", time()+$this->secondsFromHours($p['deferral'] + $p['duration'])),
					);
					if( $db->insert( $table, $values )){
						$message = $this->getLogMessage(
						"added", $this->getUserName($p['beneficiary']),
						$p['quantity'], $this->getResourceName($p['resource']),
						time() + $this->secondsFromHours( $p['deferral'] ),
						time() + $this->secondsFromHours( $p['deferral'] + $p['duration'] )
						);
						return $this->addToLog( $this->getUser(), time(), $message);
					}
				} else {
					$message = "Could not make requested booking. ";
					$message .= $this->capacityMessage( $p['resource'], $p['deferral'], $p['duration'], $available);
					$alternative = $this->getAlternativeCapacity( $p['duration'], $p['deferral'], $available );
					if ( isset( $alternative['resource'] ) && isset( $alternative['capacity'] ) ){
						$message .= $this->capacityMessage( $alternative['resource'], $p['deferral'], $p['duration'], $alternative['capacity']);
					}
					$alternative = null;
					$available = null;
					$this->messages[] = array( 'type'=>'warning','message'=>$message ) ;
					$message = null;
				}
			} else if ( 'cancel_booking' == $p['order'] &&
				isset( $p['booking_id'] ) 
				){
				$db = new ReservationDBInterface();
				$table="res_booking";
				$values=array( 'res_booking_end'=>date("Y-m-d H:i:s", time()));
				$cond = array( 'res_booking_id'=> $p['booking_id'] );
				$message = $this->getLogUpdateMessage( time(), $p['booking_id'] );
				if ( $db->update( $table, $values, $cond ) ){
					$this->addToLog( 
						$this->getUser(), 
						time(), 
						$message
						);
				}
			}
		}
	}

	private function getLogUpdateMessage( $newUnixTime, $bookingID ){
		$b = new ReservationBooking();
		$b->setID( $bookingID );
		if ($newUnixTime < $b->getUnixStart() ){
			$verb = "cancelled";
		} else {
			$verb = "stopped";
		}
		return $this->getLogMessage( $verb,
			$b->getBeneficiaryName(),
			$b->getQuantity(),
			$b->getResourceName(),
			$b->getUnixStart(),
			$b->getUnixEnd()
		);
	}

	private function getLogMessage( $verb, $beneficiary, $cores, 
		$resourceName, $unixStart, $unixEnd ){
		return $verb . " " . $cores . " on " . $resourceName . 
			" from " . $this->getLogTime( $unixStart ) . 
			" to " . $this->getLogTime( $unixEnd );
	}

	private function getLogTime( $unixTime ){
		return date($this->dateFormatUnixToLabel, $unixTime );
	}

	private function addToLog( $who, $unixWhen, $text ){
		$db = new ReservationDBInterface();
		$table="res_log";
		$values=array(
			'res_log_who'=>$who,
			'res_log_when'=>date("Y-m-d H:i:s", $unixWhen ),
			'res_log_text'=>$text
		);
		return $db->insert( $table, $values );
	}

	private function capacityMessage( $resource_id, $deferral, $duration, $units ){
		return 	"  The most you can book on " . 
			$this->getResourceName($resource_id) . 
			" from " . 
			date("Y-m-d H:i:s", time() + $this->secondsFromHours( $deferral) ) . 
			" to " . 
			date("Y-m-d H:i:s", time() + $this->secondsFromHours( $deferral  + $duration ) ) . 
			" is " . 
			$units . 
			". ";
	}

	private static function myMessage( $messageKey){
		$m = $messageKey;
		return $m;
		if ( function_exists('wfMessage') ){
			if ('' == wfMessage( $messageKey)->text()){
				return $messageKey;
			} else {
				$m=wfMessage( $messageKey)->text();
			}
		}
		return $m;
	}
	private function secondsFromHours( $s ){
		return $s * 60 * 60.0;
	}

	private function getAlternativeCapacity( $duration, $deferral, $capacity ){
		$ret = array();
		$capOld = $capacity;
		$r = $this->getResourceLabels();
		if ( count($r) > 0 ){
			foreach ($r as $key=>$label){
				$capNew = $this->get_available_capacity( $key, $duration, $deferral );
				if ( $capNew > $capOld ){
					$ret = array('resource'=>$key, 'capacity'=>$capNew);
					$capOld = $capNew;
				}
				$capNew = null;
			}
		}
		return $ret;
	}

	private function getImmediateCapacity(){
		$ret = array();
		$durs = array(1.0, 4.0, 16.0);
		foreach( $durs as $d ){
			$a = $this->getAlternativeCapacity( $d, 0, 0 );
			if ( isset( $a['resource'] ) ){
				$a['resource_name'] = $this->getResourceName($a['resource']) ; 
				unset( $a['resource'] );
				$a[] = $a['resource_name'];
				$a[] = $a['capacity'];
			}
			$a['duration'] = $d;
			$a[] = $a['duration'];
			$ret[] = $a;
		}
		return $ret;
	}

	private function get_available_capacity( $resource_id, $duration, $deferral ) {
		$cap = max(0, $this->get_max_capacity( $resource_id ) -  
			$this->get_committed_units( $resource_id, $duration, $deferral ));
		return $cap;
	}

	private function get_max_capacity( $resource_id ) {
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'capacity'=>'sum(res_resource_capacity)',
			);
		$res = $db->select(
			array('res_resource'), 
			$vars,
			array(
				'res_resource_id' => $resource_id,
				)
			);
		$max_capacity = $res[0]['capacity'];
//		print_r($max_capacity);
		return $max_capacity;
	}

	private function getUserName( $user_id ) {
		$db = new ReservationDBInterface();
		$vars = array(
			'user_name'
			);
		$res = $db->select(
			array('user'), 
			$vars,
			array(
				'user_id' => $user_id,
				)
			);
		$c = $res[0]['user_name'];
		return $c;
	}

	private function getResourceName( $resource_id ) {
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name'
			);
		$res = $db->select(
			array('res_resource'), 
			$vars,
			array(
				'res_resource_id' => $resource_id,
				)
			);
		$c = $res[0]['res_resource_name'];
		return $c;
	}

	private function get_committed_units( $resource_id, $duration, $deferral ) {
		$upperLimit = date("Y-m-d H:i:s", time() + $this->secondsFromHours( $duration + $deferral ));
		$lowerLimit = date("Y-m-d H:i:s", time()+$this->secondsFromHours( $deferral ));

		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'committed'=>'sum(res_booking_units)',
			);
		$res = $db->select(
			array('res_booking'), 
			$vars,
			array(
				"res_booking_start < '" . $upperLimit . "'",
				"res_booking_end > '" . $lowerLimit . "'",
				'res_booking_resource_id' => $resource_id,
				)
			);
		$c = $res[0]['committed'];
		return $c;
	}

  private function get_max_units(){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_capacity'=>'MAX(res_resource_capacity)',
			);
		$res = $db->select(
			array('res_resource'), 
			$vars
			);
		return $res[0][0];
	}
 
  private function getResourceLabels(){
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_id',
			);
		$ret = $db->select(
			array('res_resource'), 
			$vars,
			array(),
			__METHOD__,
			array( 'ORDER BY'=>array(
				'res_resource_name'
				)
			)
			);
		$rsort = array();
		if (count($ret)>0){
			$rsort = array();
				for ($i=0, $ii=count($ret); $i < $ii; $i++){
					$rsort[$ret[$i]['res_resource_id']]=" " . $ret[$i]['res_resource_name'];
				}
		}
		return $rsort;
	}

  private function get_quantity_labels(){
			$rsort = array();
				for ($i=0, $ii=$this->get_max_units(); $i < $ii; $i++){
					$rsort[strval($i+1)]=" " . $i+1 . " " . (0==$i ? 'core' : 'cores');
				}
		return $rsort;
	}

  private function get_duration_labels(){
			$rsort = array();
				for ($i=0, $ii=2 * $this->maxBookingDurationInHours; $i < $ii; $i++){
					$rsort[strval(($i+1.0)/2)]=" required for " . ($i+1.0)/2 . " " . (1==$i ? 'hour' : 'hours');
				}
		return $rsort;
	}

  private function get_deferral_labels(){
			$rsort = array();
				for ($i=0, $ii=24 * $this->maxBookingDeferralInDays; $i < $ii; $i++){
					$rsort[strval(($i))]=" starting " . (0==$i ? 'immediately' : 'in ' . $i . " " .(1==$i ? "hour" : "hours"));
				}
		return $rsort;
	}

  private function get_beneficiary_labels(){
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_beneficiary_name'=>'user_name',
			'res_beneficiary_id'=>'user_id',
			);
		$ret = $db->select(
			array('user'), 
			$vars,
			array(),
			__METHOD__,
			array( 'ORDER BY'=>array(
				'res_beneficiary_name'
				)
			)
			);
		$rsort = array();
		if (count($ret)>0){
			$rsort = array();
				for ($i=0, $ii=count($ret); $i < $ii; $i++){
					$rsort[$ret[$i]['res_beneficiary_id']]="for " .$ret[$i]['res_beneficiary_name'];
				}
		}
		return $rsort;
	}

	protected function get_booking_form( $unused ){
		$p = array('method'=> 'POST', 'submit'=>self::myMessage(  'Post new booking') , self::myMessage(  'reservation-post-booking'));
		$p['select'][0]['select-options'] = $this->getResourceLabels() ;
		$p['select'][0]['select-name'] = 'resource';
		$p['select'][0]['select-label'] = self::myMessage(  'res-select-resource');
		$p['select'][1]['select-options'] = $this->get_quantity_labels() ;
		$p['select'][1]['select-name'] = 'quantity';
		$p['select'][1]['select-label'] = self::myMessage(  'res-select-quantity');
		$p['select'][2]['select-options'] = $this->get_beneficiary_labels() ;
		$p['select'][2]['select-name'] = 'beneficiary';
		$p['select'][2]['select-label'] = self::myMessage(  'res-select-beneficiary');
		$p['select'][3]['select-options'] = $this->get_duration_labels() ;
		$p['select'][3]['select-name'] = 'duration';
		$p['select'][3]['select-label'] = self::myMessage(  'res-select-duration');
		$p['select'][4]['select-options'] = $this->get_deferral_labels() ;
		$p['select'][4]['select-name'] = 'deferral';
		$p['select'][4]['select-label'] = self::myMessage(  'res-select-deferral');
		$p['order'] = 'add_booking';
		$p['formLabel'] = 'New booking';
		return $p;
	}
}


