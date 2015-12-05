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
 */

$_dir = "Reservation";
foreach ( array( '',
	'/includes/',
	'/PEAR/',
	'/PEAR/HTML/',
	'/PEAR/HTML/QuickForm2',
	) AS $path ){
	set_include_path(get_include_path(). PATH_SEPARATOR. dirname(__FILE__). "/". $_dir. $path );
}

function Reservation_autoloader($class, $file){
	if (!class_exists($class) && !interface_exists($class)){
		require_once ($file);
		if (!class_exists($class) && !interface_exists($class)){
			throw new Exception("Can't instantiate " . $class . " from " . $file . " in ". __FILE__ );
		}
//		$o = new $class;
	}
}

// if class A requires class B then put the Reservation_autoloader call to class A below the call to class B
Reservation_autoloader("HTML_QuickForm2",$_dir . "/PEAR/HTML/QuickForm2.php");
Reservation_autoloader("Validate", $_dir  . "/PEAR/Validate.php");
Reservation_autoloader(  		"HTML_Common", "PEAR/HTML/Common.php");
Reservation_autoloader(  		"HTML_Table", "PEAR/HTML/Table.php");
Reservation_autoloader(		"Validate", "PEAR/Validate.php");
Reservation_autoloader(		"ReservationObject", "includes/ReservationObject.php");
Reservation_autoloader(		"ReservationConcept",	"includes/ReservationConcept.php");
Reservation_autoloader(		"ReservationController", "includes/ReservationController.php");
//Reservation_autoloader(		"ReservationXML", "includes/ReservationXML.php");
//Reservation_autoloader(		"ReservationResource", "includes/ReservationResource.php");
//Reservation_autoloader(		"ReservationBooking",	"includes/ReservationBooking.php");
//Reservation_autoloader(		"ReservationCollection", "includes/ReservationCollection.php");
//Reservation_autoloader(		"ReservationResources", "includes/ReservationResources.php");
//Reservation_autoloader(		"ReservationBookings", "includes/ReservationBookings.php");
//Reservation_autoloader(		"ReservationRender", "includes/ReservationRender.php");

	
