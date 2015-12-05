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
 * ReservationController is for the form used to input and process
 * requests about Reservation objects.
 *
 */
class ReservationController implements ReservationConcept {

	private $maxBookingDurationInHours = 168;
	private $maxBookingDeferralInDays = 60;
	
	private $blankTime = "";
	private $dateFormat = "%H:%i %W %e %b %y";
	private function secondsFromHours( $s ){
		return $s * 60 * 60.0;
	}

	protected static function myMessage( $messageKey){
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


	public function __construct(){
	}

	public function processPost( $parameters) {
		$p = $parameters;
		if( isset( $p['resource'] ) &&
			isset( $p['beneficiary'] ) &&
			isset( $p['quantity'] ) &&
			isset( $p['duration'] ) &&
			isset( $p['deferral'] ) &&
			isset( $p['order'] )
		){
			if ( 'add_booking' == $p['order'] ){
				$db = new ReservationDBInterface();
				$table="res_booking";
				$values=array(
				'res_booking_resource_id'=>$parameters['resource'],
				'res_booking_beneficiary_id'=>$parameters['beneficiary'],
				'res_booking_units'=>$parameters['quantity'],
				'res_booking_start'=>date("Y-m-d H:i:s", time() + $this->secondsFromHours( $parameters['deferral']) ),
				'res_booking_end'=>date("Y-m-d H:i:s", time()+$this->secondsFromHours($parameters['deferral'] + $parameters['duration'])),
				);
				return $db->insert( $table, $values );
			} else {
				return;
			}
		}
	}

	public function get_controller( $parameters) {
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_booking_units',
	 		'res_beneficiary_name',	
			'res_booking_startF'=>
				'IF( res_booking_start > now(),
					DATE_FORMAT(res_booking_start, "' . $this->dateFormat . '"),"' . 
					$this->blankTime . '")',
			'res_booking_endF'=>
				'DATE_FORMAT(res_booking_end, "' . $this->dateFormat . '")',
			);
		$bookings = $db->select(
			array('res_booking','res_resource','res_unit','res_beneficiary'), 
			$vars,
			array(
				'res_booking_beneficiary_id=res_beneficiary_id',
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
		$result['output']['unrendered']['table']['bookings']['data'] = $bookings;
		$result['output']['unrendered']['table']['bookings']['header'] = array(
			'Resource','Units','For','Start','Stop',
		);
	  $result['output']['unrendered']['forms'][] = array(
			'content'=> $this->get_booking_form( NULL ),
			'type'=>'select',
			);
		return $result;
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
 
  private function get_resource_labels(){
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
			'res_beneficiary_name',
			'res_beneficiary_id',
			);
		$ret = $db->select(
			array('res_beneficiary'), 
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
		$p['select'][0]['select-options'] = $this->get_resource_labels() ;
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


