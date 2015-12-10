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

	public function __construct( User $user ){
		$this->user = $user;
//echo "user " . print_r($user->getName(),1) . print_r( $user->getRights(),1 );
		$this->messages = array();
	}

	public function get_controller( $parameters) {
		$this->processPost( $parameters );
		return $this->getPage();
	}

  private function getPage(){
		$result['output']['unrendered']['table'] = $this->getTables(); 
	if ($this->getUser()->isLoggedIn()){
	  $result['output']['unrendered']['forms'][] = array(
			'content'=> $this->get_booking_form( NULL ),
			'type'=>'select',
			);
	  $result['output']['unrendered']['forms'][] = array(
			'content'=> $this->get_booking_form_overnight( NULL ),
			'type'=>'select',
			);
	}
		$result['messages'] = $this->messages;
		return $result;
	}

  private function getTables(){
		$r = array();
#		$r['immediate'] = $this->getImmediateCapacity();
		$r['bookings'] = $this->getBookings();
		$r['log'] = $this->getLog();
		$r['usage'] = $this->getUsage();
		$r['current-usage'] = $this->getCurrentUsage();
		$r['your-blades'] = $this->getYourBlades();
		$r['all-blades'] = $this->getAllBlades();
		return $r;
	}

	private function getYourBlades() {
		$b = new ReservationBeneficiary( $this->user );
		$r = $b->getResourceRightList();
		if ( 0 == count( $r ) ){
			if ($this->getUser()->isLoggedIn()){
			$this->messages[] = array( 'type'=>'warning',
				'message'=>wfMessage('reservation-no-bookable-blades')->text());
			} else {
			$this->messages[] = array( 'type'=>'warning',
				'message'=>wfMessage('reservation-login-for-bookable-blades')->text());
			}

		} 
		$ret['data'] = $r;
		$ret['header'] = array(
			wfMessage('reservation-label-blade')->text(),
			wfMessage('reservation-label-total-cores')->text(),
			wfMessage('reservation-label-group-right')->text()
		);
		return $ret;
	}

	private function getAllBlades() {
		$r = $this->getResourceRights();
		$ret['data'] = $r;
		$ret['header'] = array(
			wfMessage('reservation-label-blade')->text(),
			wfMessage('reservation-label-total-cores')->text(),
			wfMessage('reservation-label-group-right')->text()
		);
		return $ret;
	}

	private function getUser(){
		return $this->user;
	}
		
	private function getBookings(){
		$marginInSeconds = 60;
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_booking_units',
	 		'res_beneficiary_name'=>'user_name',	
			'res_booking_startF'=>
				'IF( res_booking_start < DATE_ADD( now(), INTERVAL ' . $marginInSeconds . ' SECOND ), "' . wfMessage('reservation-label-current')->text() . '",DATE_FORMAT(res_booking_start, "' . $this->dateFormatMySQL . '"))',
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
				'IF( res_booking_start < DATE_ADD( now(), INTERVAL ' . $marginInSeconds . ' SECOND) ,0,1)','res_resource_name','res_booking_units DESC',
				'res_booking_end','res_beneficiary_name'
				)
			)
			);
		$ret['data'] = $bookings;
		$ret['header'] = array(
						wfMessage('reservation-label-blade')->text(),
						wfMessage('reservation-label-cores')->text(),
						wfMessage('reservation-label-for')->text(),
						wfMessage('reservation-label-start')->text(),
						wfMessage('reservation-label-stop')->text(),
						wfMessage('reservation-label-cancel')->text(),
		);
		$ret['delete'] = $this->getUser()->isLoggedIn();
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
			array( 'LIMIT'=>100, 'ORDER BY'=>array('res_log_when DESC')
)
			);
		$ret['data'] = $res;
		$ret['header'] = array(
						wfMessage('reservation-label-who')->text(),
						wfMessage('reservation-label-when')->text(),
						wfMessage('reservation-label-what')->text(),
		);
		return $ret;
	}

	private function getCurrentUsage(){
		$marginInSeconds = 60;
		$ret = array();
		$db = new ReservationDBInterface();
              	$vars = array(
				'res_resource_name', 
				'unit_hours'=>'SUM(res_booking_units)',
			);
//print_r($vars);
		$res = $db->select(
			array('res_booking','res_resource'), 
			$vars,
			array('res_resource_id=res_booking_resource_id',
				"res_booking_end > DATE_SUB( now(), INTERVAL " . $marginInSeconds . " SECOND )",
				"res_booking_start < DATE_ADD( now(), INTERVAL " . $marginInSeconds . " SECOND)",
				),
			__METHOD__,
			array( 
				'GROUP BY'=> array('res_booking_resource_id'),
			 	'ORDER BY'=> array('res_resource_name'),
			)

			);
		$ret['data'] = $res;
		$ret['header'] = array(
						wfMessage('reservation-label-blade')->text(),
						wfMessage('reservation-label-cores')->text(),
		);
		return $ret;
	}

	private function getUsage(){
		$marginInSeconds = 5;
		$ret = array();
		$db = new ReservationDBInterface();
              	$vars = array(
				'user_name', 
				'unit_hours'=>'SUM(res_booking_units/3600*IF(unix_timestamp(res_booking_end)>unix_timestamp(res_booking_start), unix_timestamp(res_booking_end)-unix_timestamp(res_booking_start),0))',
				'timezone'=>"IF(res_booking_end < DATE_ADD( now(), INTERVAL " . $marginInSeconds . " SECOND ),'past',IF(res_booking_start > DATE_SUB( now(), INTERVAL " . $marginInSeconds . " SECOND),'future','now'))"
			);
//print_r($vars);
		$res = $db->select(
			array('res_booking','user'), 
			$vars,
			array('user_id=res_booking_beneficiary_id'),
			__METHOD__,
			array( 
				'GROUP BY'=> array('res_booking_beneficiary_id', 'timezone'),
			 	'ORDER BY'=> array('timezone','unit_hours DESC'),
			)

			);
		$ret['data'] = $res;
		$ret['header'] = array(
						wfMessage('reservation-label-for')->text(),
						wfMessage('reservation-label-core-hours')->text(),
						wfMessage('reservation-label-when')->text(),
		);
		return $ret;
	}

	private function processPost( $p) {
		if( isset( $p['order'] )){
			$b = new ReservationBooking( $this->user );
			if ( 'add_booking_overnight' == $p['order'] ){
				$this->messages[] = $b->submitBookingOvernight( $p );
			} else if ( 'add_booking' == $p['order'] ){
				$this->messages[] = $b->submitBooking( $p );
			} else if ( 'cancel_booking' == $p['order'] ){
				$this->messages[] = $b->cancelBooking( $p );
			}
		}
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
				for ($i=1, $ii=$this->get_max_units(); $i <= $ii; $i = 2*$i){
					$rsort[strval($i)]=" " . $i . " " . (1==$i ? wfMessage('reservation-label-core')->text() : wfMessage('reservation-label-core-plural')->text() );
				}
		return $rsort;
	}

  private function get_duration_labels(){
			$rsort = array();
				for ($i=0, $ii=4 * $this->maxBookingDurationInHours; $i < $ii; $i++){
					$rsort[strval(($i+1.0)/4)]=" " . wfMessage('reservation-label-required-for')->text() . " " . ($i+1.0)/4 . " " . (1==$i ? wfMessage('reservation-label-hour')->text() : wfMessage('reservation-label-hour-plural')->text());
				}
		return $rsort;
	}

  private function get_deferral_labels(){
			$rsort = array();
				for ($i=0, $ii=24 * $this->maxBookingDeferralInDays; $i < $ii; $i++){
					$rsort[strval(($i))]=" " . wfMessage('reservation-label-starting')->text() . " " . (0==$i ? wfMessage('reservation-label-immediately')->text() : wfMessage('reservation-label-in')->text() . $i . " " .(1==$i ? wfMessage('reservation-label-hour')->text() : wfMessage('reservation-label-hour-plural')->text()));
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
		$p = array('method'=> 'POST', 'submit'=>self::myMessage(  'Get-new-booking') , self::myMessage(  'reservation-post-booking'));
/*
		$p['select'][0]['select-options'] = $this->getResourceLabels() ;
		$p['select'][0]['select-name'] = 'resource';
		$p['select'][0]['select-label'] = self::myMessage(  'res-select-resource');
*/
		$p['select'][1]['select-options'] = $this->get_quantity_labels() ;
		$p['select'][1]['select-name'] = 'quantity';
		$p['select'][1]['select-label'] = self::myMessage(  'res-select-quantity');
/*
		$p['select'][2]['select-options'] = $this->get_beneficiary_labels() ;
		$p['select'][2]['select-name'] = 'beneficiary';
		$p['select'][2]['select-label'] = self::myMessage(  'res-select-beneficiary');
*/
		$p['select'][3]['select-options'] = $this->get_duration_labels() ;
		$p['select'][3]['select-name'] = 'duration';
		$p['select'][3]['select-label'] = self::myMessage(  'res-select-duration');
		$p['select'][4]['select-options'] = $this->get_deferral_labels() ;
		$p['select'][4]['select-name'] = 'deferral';
		$p['select'][4]['select-label'] = self::myMessage(  'res-select-deferral');
		$p['order'] = 'add_booking';
		$p['formLabel'] = wfMessage('reservation-label-new-booking')->text();
		return $p;
	}

	protected function get_booking_form_overnight( $unused ){
		$p = array('method'=> 'POST', 'submit'=>self::myMessage(  'Get-overnight-booking') , self::myMessage(  'reservation-post-booking-overnight'));
		$p['select'][1]['select-options'] = $this->get_quantity_labels() ;
		$p['select'][1]['select-name'] = 'quantity';
		$p['select'][1]['select-label'] = self::myMessage(  'res-select-quantity');
		$p['order'] = 'add_booking_overnight';
		$p['formLabel'] = wfMessage('reservation-label-new-overnight-booking')->text();
		return $p;
	}

	public function getResourceRights(){
		$res = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_capacity',
			'res_group_right',
			);
		$res = $db->select(
			array('res_group','res_resource'), 
			$vars,
			array(
				'res_resource_group_id=res_group_id',
				),
			__METHOD__,
			array( 'ORDER BY'=>array(
				'res_group_right','res_resource_name'
				)
			));
		return $res;
	}



}


