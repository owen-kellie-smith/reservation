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
 *
 * @file
 * @author     Owen Kellie-Smith
 */

/**
 * 
 * @class ReservationBooking 
 *
 */
class ReservationBooking extends ReservationObject {

	private $user;
  private $beneficiaryName;
	private $bookingID;
	private $resourceID;
	private $resourceName;
	private $units;
	private $unixEnd;
	private $unixStart;
	private $isDirty;
	private $dateFormat = "%H:%i %W %e %b %y";
	private $dateFormatUnixToLabel = "H:i D d-M-Y";
	private $minPerInt=15;
	private $hourNightStarts = 17;
	private $hourNightEnds = 10;
	
	private function roundedUpUnixTime( $seconds ){
		return ceil($seconds / ($this->minPerInt * 60)) * ($this->minPerInt * 60); 
	}

	private function roundedDownUnixTime( $seconds ){
		return floor($seconds / ($this->minPerInt * 60)) * ($this->minPerInt * 60); 
	}

	public function __construct( User $user ){
		$this->user = $user;
	}

	private function getUser(){
		return $this->user;
	}
	public function getBeneficiaryName(){
		return $this->beneficiaryName;
	}

/*
	public function getBookingID(){
		return $this->bookingID;
	}
*/

	public function getResourceID(){
		return $this->resourceID;
	}

	public function getQuantity(){
		return $this->units;
	}

	public function getUnixEnd(){
		return $this->unixEnd;
	}

	public function getUnixStart(){
		return $this->unixStart;
	}

	private function nullify(){
		$this->resourceName = null;
		$this->resourceID = null;
		$this->unixStart = null;
		$this->unixEnd = null;
		$this->beneficiaryName = null;
		$this->units = null;
		$this->bookingID = null;
		$this->isDirty = null;
	}		
	
	public function setID( $i ){
		$this->nullify();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_id',
			'res_booking_units',
	 		'res_beneficiary_name'=>'user_name',	
			'res_booking_startU'=>"UNIX_TIMESTAMP(res_booking_start)",
			'res_booking_endU'=>"UNIX_TIMESTAMP(res_booking_end)",
			);
		$res = $db->select(
			array('res_booking','res_resource','res_unit','user'), 
			$vars,
			array(
				'res_booking_beneficiary_id=user_id',
				'res_booking_resource_id=res_resource_id',
				'res_resource_unit_id=res_unit_id',
				'res_booking_id'=>$i,
				)
			);
//print_r($res);
		if (isset($res[0])){
			$this->bookingID = $i;
		}
		if (isset($res[0]['res_resource_name'])){
			$this->resourceName = $res[0]['res_resource_name'];
		}
		if (isset($res[0]['res_resource_id'])){
			$this->resourceID = $res[0]['res_resource_id'];
		}
		if (isset($res[0]['res_booking_units'])){
			$this->units = $res[0]['res_booking_units'];
		}
		if (isset($res[0]['res_beneficiary_name'])){
			$this->beneficiaryName = $res[0]['res_beneficiary_name'];
		}
		if (isset($res[0]['res_booking_startU'])){
			$this->unixStart = $res[0]['res_booking_startU'];
		}
		if (isset($res[0]['res_booking_endU'])){
			$this->unixEnd = $res[0]['res_booking_endU'];
		}
	}

  private function submitBookingFixedStartStop( $p ){
		$alternative = $this->getAlternativeCapacityA( $p['unixStart'], $p['unixEnd'], $p['quantity'] - 0.5 ); # seek a capacity strictly higher;
		if ( isset( $alternative['resource'] ) && isset( $alternative['capacity'] ) ){
			$p['resource'] = $alternative['resource'];
			$p['beneficiary'] = $this->user->getID();
			$message = $this->saveBookingA($p);
			return $message;
		} else {
			$message = "The system does not have capacity for your request.  Are you logged in? ";
			return array( 'type'=>'warning','message'=>$message ) ;
		}
	}

  public function submitBookingOvernight( $p ){
		if (  isset( $p['quantity'] )  ){
			$_now = time();
			$p['unixStart'] = $this->unixTimeStartTonightBooking();
			$p['unixEnd'] = $this->unixTimeTomorrowMOrning();
  			return $this->submitBookingFixedStartStop( $p );
		} else {
			$message = "Quantity / duration / deferral not set? Could not make any booking. ";
			return array( 'type'=>'warning','message'=>$message ) ;
		}
	}

	private function unixTimeStartTonightBooking(){
		return max(time(), $this->unixTimeStartTonight() );
	}

	private function unixTimeStartTonight(){
		$_now = time();
		$hourNow = date("H", $_now);
		$minNow = date("i", $_now);
		$secNow = date("s", $_now);
		return $_now - $secNow 
			- 60 * $minNow  
			- 60 * 60 * $hourNow
			+ 60 * 60 * $this->hourNightStarts;
	}

	private function unixTimeTomorrowMorning(){
		return $this->unixTimeStartTonight()
		 + 60 * 60 * (24 + $this->hourNightEnds - $this->hourNightStarts);
	}

	
  public function submitBooking( $p ){
		if (  isset( $p['quantity'] ) &&
			isset( $p['duration'] ) &&
			isset( $p['deferral'] ) ){
			$_now = time();
			$p['unixStart'] = $this->roundedDownUnixTime( $_now + $this->secondsFromHours( $p['deferral'] ) );
			$p['unixEnd'] = $this->roundedUpUnixTime( $_now + $this->secondsFromHOurs( $p['deferral'] + $p['duration'] ) );
  			return $this->submitBookingFixedStartStop( $p );
		} else {
			$message = "Quantity / duration / deferral not set? Could not make any booking. ";
			return array( 'type'=>'warning','message'=>$message ) ;
		}
	}
  

	private function saveBookingA($p){
		$db = new ReservationDBInterface();
		$table="res_booking";
		$values=array(
			'res_booking_resource_id'=>$p['resource'],
			'res_booking_beneficiary_id'=>$p['beneficiary'],
			'res_booking_units'=>$p['quantity'],
			'res_booking_start'=>date("Y-m-d H:i:s", $p['unixStart'] ),
			'res_booking_end'=>date("Y-m-d H:i:s", $p['unixEnd']),
			);
		if( $db->insert( $table, $values )){
			$message = $this->getLogMessage(
			"added", $this->getUserName($p['beneficiary']),
			$p['quantity'], $this->getResourceName($p['resource']),
			$p['unixStart'],
			$p['unixEnd']
			);
			$this->addToLog( $this->getUser()->getName(), time(), $message);
				$message=null;
			$bkMessage = "You are booked on " . $this->getResourceName($p['resource']);
			return array( 'type'=>'success','message'=>$bkMessage ) ;
		}	
	}

  public function cancelBooking( $p ){
			if ( isset( $p['booking_id'] ) ){
				$db = new ReservationDBInterface( $this->getUser() );
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

	private function getLogUpdateMessage( $newUnixTime, $bookingID ){

		$b = new ReservationBooking( $this->getUser() );
		$b->setID( $bookingID );
//print_r( $newUnixTime );
//print_r( $b->getUnixStart() );
		if ($newUnixTime < $b->getUnixStart() ){
			$verb = "cancelled";
		} else {
			$verb = "stopped";
		}
		return $this->getLogMessage( $verb,
			$b->getBeneficiaryName(),
			$b->getQuantity(),
			$b->getResourceName( $b->getResourceID() ),
			$b->getUnixStart(),
			$b->getUnixEnd()
		);
	}

	private function getLogMessage( $verb, $beneficiary, $cores, 
		$resourceName, $unixStart, $unixEnd ){
		return $verb . " " . $cores . " on " . $resourceName . 
			" for " . $beneficiary .
			" from " . $this->getLogTime( $unixStart ) . 
			" to " . $this->getLogTime( $unixEnd );
	}

	private function getLogTime( $unixTime ){
		if (is_numeric( $unixTime ) ){
			return date($this->dateFormatUnixToLabel, $unixTime );
		} else {
			return $unixTime;
		}
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

	private function getAlternativeCapacityA( $unixStart, $unixEnd, $capacity ){
		$ret = array();
		$capOld = $capacity;
		$ben = new ReservationBeneficiary( $this->getUser() );
		$r = $ben->getAllowableResources();
		if ( count($r) > 0 ){
			foreach ($r as $key=>$res){
				if (isset( $res['res_resource_id'] ) ){
					$capNew = $this->get_available_capacity_a( $res['res_resource_id'], $unixStart, $unixEnd );
					if ( $capNew > $capOld ){
						$ret = array('resource'=>$res['res_resource_id'], 'capacity'=>$capNew);
						$capOld = $capNew;
					}
					$capNew = null;
				}
			}
		}
		return $ret;
	}


	private function getImmediateCapacity(){
		$placeC=1; $placeD=0; $placeR = 2;
		$result = array();
		$ret = array();
		$header = array();
		$durs = array(1.0, 4.0, 16.0);
		foreach( $durs as $d ){
			$a = $this->getAlternativeCapacity( $d, 0, 0 );
			if ( isset( $a['resource'] ) ){
				$a['resource_name'] = $this->getResourceName($a['resource']) ; 
				unset( $a['resource'] );
				$a[$placeR] = $a['resource_name'];
				$a[$placeC] = $a['capacity'];
			}
			$a['duration'] = $d;
			$a[$placeD] = $a['duration'];
			$ret[] = $a;
		}
		$result['data'] = $ret;
		$header[$placeR] = 'Resource';
		$header[$placeC] = 'Cores available';
		$header[$placeD] = 'Hours required, starting at ' . date("Y-m-d H:i", time() )  ;
		$result['header'] = $header;
		return $result;
	}

	private function get_available_capacity_a( $resource_id, $unixStart, $unixEnd ) {
		$cap = max(0, $this->get_max_capacity( $resource_id ) -  
			$this->get_committed_units_a( $resource_id, $unixStart, $unixEnd ));
		return $cap;
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
		if (isset ($res[0]['capacity']) ){
			$max_capacity = $res[0]['capacity'];
			return $max_capacity;
		}
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
		if (isset ($res[0]['res_resource_name']) ){
			$c = $res[0]['res_resource_name'];
			return $c;
		}
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
		if (isset ($res[0]['user_name']) ){
			$c = $res[0]['user_name'];
			return $c;
		}
	}

	private function get_committed_units_a( $resource_id, $unixStart, $unixEnd ) {
		$upperLimit = date("Y-m-d H:i:s", $unixEnd);
		$lowerLimit = date("Y-m-d H:i:s", $unixStart);

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

  public function getResourceLabels(){
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
			
} // end of class

