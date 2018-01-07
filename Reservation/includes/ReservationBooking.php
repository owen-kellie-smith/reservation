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

	public function __construct( User $user ){
		$this->user = $user;
	}

	private function getUser(){
		return $this->user;
	}

	public function getBeneficiaryName(){
		return $this->beneficiaryName;
	}

	public function getBookingID(){
		return $this->bookingID;
	}

	public function getResourceID(){
		return $this->resourceID;
	}

	public function getResourceName(){
		return $this->resourceName;
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

  public function submitBooking( $p ){
			if (  isset( $p['quantity'] ) &&
						isset( $p['duration'] ) &&
						isset( $p['deferral'] ) ){
					$alternative = $this->getAlternativeCapacity( $p['duration'], $p['deferral'], $p['quantity'] - 0.5 ); # seek a capacity strictly higher;
					if ( isset( $alternative['resource'] ) && isset( $alternative['capacity'] ) ){
						$p['resource'] = $alternative['resource'];
						$p['beneficiary'] = $this->user->getID();
						$this->saveBooking($p);
					}
				} else {
					$message = "Could not make requested booking. ";
					$this->messages[] = array( 'type'=>'warning','message'=>$message ) ;
					$message = null;
				}
		}
  

		private function saveBooking($p){
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
							return $this->addToLog( $this->getUser()->getName(), time(), $message);
						}	
			}

  private function cancelBooking( $p ){
			if ( isset( $p['booking_id'] ) ){
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

	private function getLogUpdateMessage( $newUnixTime, $bookingID ){

		$b = new ReservationBooking();
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
			$b->getResourceName(),
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

