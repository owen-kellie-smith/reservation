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


	public function __construct(){
	}

	public function get_controller( $parameters) {
		$ret = array();
		$db = new ReservationDBInterface();
		$vars = 	array('res_resource_name',
						'res_booking_units',
	 					'res_beneficiary_name','res_booking_start','res_booking_end',
						);
		$bookings = $db->select(
			array('res_booking','res_resource','res_unit','res_beneficiary'), 
			$vars,
			array(
				'res_booking_beneficiary_id=res_beneficiary_id',
				'res_booking_resource_id=res_resource_id',
				'res_resource_unit_id=res_unit_id',
				),
			__METHOD__,
			array( 'ORDER BY'=>array(
				'res_resource_name','res_booking_start','res_booking_end',
				'res_booking_units','res_beneficiary_name'
				)
			)
			);
		$result['output']['unrendered']['table']['bookings']['data'] = $bookings;
		$result['output']['unrendered']['table']['bookings']['key'] = $vars;
		$result['output']['unrendered']['table']['bookings']['header'] = array(
			'Resource','Units','For','Start','Stop',
		);
		return $result;
	}


}


