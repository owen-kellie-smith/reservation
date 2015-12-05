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
/**
 * SpecialPage for Reservation extension
 * Hack of BoilerPlate
 *
 */

$_dir = "";
foreach ( array( '',
        '/includes/',
        '/PEAR/',
        '/PEAR/HTML/',
        '/PEAR/HTML/QuickForm2',
        ) AS $path ){
        set_include_path(get_include_path(). PATH_SEPARATOR. dirname( dirname(__FILE__)). "/". $_dir. $path );
}

class SpecialReservation extends SpecialPage {

	private $showUglyDebugMessagesOnRenderedPage=false;

	public function __construct() {
		parent::__construct( 'Reservation' );
	}

	public function execute( $sub ) {
		$out = $this->getOutput();
		$this->outputGreetings( $out );
		$result = $this->getResult();
		$this->outputDebugMessagesIfRequired( $out, $result );
		$this->outputResult( $out, $result );
	}

	protected function getGroupName() {
		return 'other';
	}

	private function getResult(){
		$m = new ReservationController();
		$m->processPost($this->getRequest()->getValues());
		return $m->get_controller($this->getRequest()->getValues()) ; 
	}

	private function outputGreetings( &$out ){
		$out->setPageTitle( $this->msg( 'reservation-helloworld' ) );
//		$out->addWikiMsg( 'reservation-helloworld-intro' );
//		$out->addHTML( $this->restartForm() );
	}

	private function outputDebugMessagesIfRequired( &$out, $result ){
		if ($this->showUglyDebugMessagesOnRenderedPage){
			$out->addHTML( "getVaues is <pre> " . 
				print_r($this->getRequest()->getValues(), 1) . "</pre>" 
			);
/*
			$out->addHTML( "getQueryVaues is <pre> " . 
				print_r($this->getRequest()->getQueryValues(), 1) . "</pre>" 
			);
			$out->addHTML( "getRawPostString is <pre> " . 
				print_r($this->getRequest()->getRawPostString(), 1) . "</pre>" 
			);
			$out->addHTML( "getArray POST <pre> " . 
				print_r($this->getRequest()->getArray('POST'), 1) . "</pre>" 
			);
*/
/*
			$out->addHTML( "result is <pre> " . 
				print_r($result, 1) . "</pre>" 
			);
*/
		}
	}

	private function outputResult( &$out, $result ){
		$render = new ReservationRender();
		if (isset($result['warning'])){
			$out->addHTML( "<span class='reservation-warning'>" . 
				$result['warning'] . "</span>"
			);
		} else {
			$u = array();
			if (isset($result['output']['unrendered'])){
				$u = $result['output']['unrendered'];
			}
			$res = $render->get_rendered_result( 
				$u, ''
			);
			if (isset($res['forms'])){
				foreach ($res['forms'] AS $_f){
						$out->addHTML( $_f ); 
				}
			}
			if (isset($res['bookings'])){
            			$out->addHTML( $res['bookings'] );
			}
		}
		return;
	}

	private function restartForm(){
		$_restart_label = wfMessage( 'reservation-restart')->text();
		return '<form action="" method=GET><input type="submit" value="' . 
			$_restart_label . '"></form>' ;
	}

}
