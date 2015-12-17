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

$_dir = "";
foreach ( array( '',
	'/includes/',
	'/PEAR/',
	'/PEAR/HTML/',
	'/PEAR/HTML/QuickForm2',
	) AS $path ){
	set_include_path(get_include_path(). PATH_SEPARATOR. dirname(__FILE__). "/". $_dir. $path );
}

function Reservationautoloader($class, $file){
	if (!class_exists($class) && !interface_exists($class)){
		require_once ($file);
		if (!class_exists($class) && !interface_exists($class)){
			throw new Exception("Can't instantiate " . $class . " from " . $file . " in ". __FILE__ );
		}
//		$o = new $class;
	}
}

$_dir = dirname(__FILE__);
// if class A requires class B then put the Reservationautoloader call to class A below the call to class B
//Reservationautoloader("HTML_QuickForm2",$_dir . "/PEAR/HTML/QuickForm2.php");
//Reservationautoloader("Validate", $_dir  . "/PEAR/Validate.php");
//Reservationautoloader(  		"HTML_Table", "PEAR/HTML/Table.php");
//Reservationautoloader(		"Validate", "PEAR/Validate.php");
Reservationautoloader(		"ReservationBooking", "includes/ReservationBooking.php");
Reservationautoloader(		"ReservationController", "includes/ReservationController.php");

	
