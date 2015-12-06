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
	private $start;
	private $end;
	private $beneficiaryName;
	private $units;
	private $dateFormat = "%H:%i %W %e %b %y";

	public function __construct(){
	}

	public function getStart(){
		return $this->start;
	}

	public function getEnd(){
		return $this->end;
	}

	public function getQuantity(){
		return $this->units;
	}

	public function getResourceName(){
		return $this->resourceName;
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
			'res_booking_startF'=>
				'DATE_FORMAT(res_booking_start, "' . $this->dateFormat . '")',
			'res_booking_endF'=>
				'DATE_FORMAT(res_booking_end, "' . $this->dateFormat . '")',
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
		if (isset($res[0]['res_booking_startF'])){
			$this->start = $res[0]['res_booking_startF'];
		}
		if (isset($res[0]['res_booking_endF'])){
			$this->end = $res[0]['res_booking_endF'];
		}
	}
					
} // end of class

