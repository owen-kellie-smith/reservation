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

	public function execute( $unused ) {
		$this->displayPage( $this->getOutput(), $this->getReservations() );
		if( !empty($_POST) ){
//			$this->getOutput()->redirect( $this->getTitle()->getFullURL() );
		}
	}

	protected function getGroupName() {
		return 'other';
	}

	private function getReservations(){
		$m = new ReservationController( $this->getUser(), $this->getTitle() );
		return $m->get_controller($this->getRequest()->getValues()) ; 
	}

	private function displayPage( $out, $res ){
		$this->outputGreetings( $out );
		$this->outputDebugMessagesIfRequired( $out, $res );
		$this->outputReservations( $out, $res );
	}

	private function outputGreetings( &$out ){
		$out->setPageTitle( $this->msg( 'reservation-helloworld' ) );
		$out->addWikiMsg( 'reservation-helloworld-intro' );
//		$out->addHTML( $this->restartForm() );
	}

	private function outputDebugMessagesIfRequired( &$out, $result ){
		if ($this->showUglyDebugMessagesOnRenderedPage){
			$out->addHTML( "s->getUser()->getGroups(): <pre> " . 
				print_r($this->getUser()->getGroups(), 1) . "</pre>" 
			);
			$out->addHTML( "s->getUser()->getRights(): <pre> " . 
				print_r($this->getUser()->getRights(), 1) . "</pre>" 
			);
			$b = new ReservationBeneficiary( $this->getUser() );
			$out->addHTML( "beneficiary->getALlowableResources(): <pre> " . 
				print_r($b->getAllowableResources(), 1) . "</pre>" 
			);
			$out->addHTML( "s->getRequest()->getValues(): <pre> " . 
				print_r($this->getRequest()->getValues(), 1) . "</pre>" 
			);
			$out->addHTML( "result: <pre> " . 
				print_r($result, 1) . "</pre>" 
			);
		}
	}

	private function outputReservations( &$out, $res ){
		if (isset($res['messages'])){
			if ( count( $res['messages'] ) > 0 ) {
				foreach ( $res['messages']  AS $w ) {
					if ( isset( $w['type'] )  && isset( $w['message'])   ){
						if ( 'warning'== $w['type'] ){
							$out->addHTML( "<span class='res-warning'>" . $w['message'] . "</span>" ) ;
						}
						if ( 'success'== $w['type'] ){
							$out->addHTML( "<span class='res-success'>" . $w['message'] . "</span>" ) ;
						}
					}
				}
			}
		}
		$render = new ReservationRender();
		if (isset($res['output']['unrendered'])){
			$res = $render->get_rendered_result( 
				$res['output']['unrendered'], ''
			);
		}
		if (isset($res['immediate'])){
			$out->addWikiMsg( 'reservation-section-available-cores' );
            		$out->addHTML( $res['immediate'] );
		}
		if (isset($res['forms'])){
			foreach ($res['forms'] AS $_f){
					$out->addHTML( $_f ); 
			}
		}
		if (isset($res['current-usage'])){
			$out->addWikiMsg( 'reservation-section-cores-booked' );
            		$out->addHTML( $res['current-usage'] );
		}
		if (isset($res['bookings'])){
			$out->addWikiMsg( 'reservation-section-future-bookings' );
            		$out->addHTML( $res['bookings'] );
		}
		if (isset($res['your-blades'])){
			$out->addWikiMsg( 'reservation-section-blades-available-for-you' );
			$out->addWikiMsg( 'reservation-see-userrights' );
            		$out->addHTML( $res['your-blades'] );
		}
/*
		if (isset($res['usage'])){
			$out->addWikiText("==Usage by person==");
            		$out->addHTML( $res['usage'] );
		}
		if (isset($res['log'])){
			$out->addWikiText("==Latest log==");
            		$out->addHTML( $res['log'] );
		}
*/
			$out->addWikiMsg( 'reservation-see-log' );
	}
}
