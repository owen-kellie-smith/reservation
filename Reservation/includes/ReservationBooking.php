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

	private $resourceName;
	private $resourceID;
	private $unixstart;
	private $unixend;
	private $beneficiaryName;
	private $units;
	private $dateFormat = "%H:%i %W %e %b %y";

	public function __construct(){
	}

	public function getUnixStart(){
		return $this->unixstart;
	}

	public function getUnixEnd(){
		return $this->unixend;
	}

	public function getQuantity(){
		return $this->units;
	}

	public function getResourceName(){
		return $this->resourceName;
	}

	public function getBeneficiaryName(){
		return $this->beneficiaryName;
	}

	private function nullify(){
		$this->resourceName = null;
		$this->resourceID = null;
		$this->start = null;
		$this->end = null;
		$this->beneficiaryName = null;
		$this->units = null;
	}
		
	
	public function setID( $i ){
		$this->nullify();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_id',
			'res_booking_units',
	 		'res_beneficiary_name'=>'user_name',	
			'res_booking_startU'=>"UNIX_TIMESTAMP(STR_TO_DATE(res_booking_start, '%M %d %Y %h:%i%p'))",
			'res_booking_endU'=>"UNIX_TIMESTAMP(STR_TO_DATE(res_booking_end, '%M %d %Y %h:%i%p'))",
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
			$this->unixstart = $res[0]['res_booking_startU'];
		}
		if (isset($res[0]['res_booking_endU'])){
			$this->unixend = $res[0]['res_booking_endU'];
		}
	}
					
} // end of class

