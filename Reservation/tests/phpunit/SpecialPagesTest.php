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
 * @group Reservation
 */
class SpecialPageTest extends MediaWikiTestCase{
  
	private $unused;

  public function setup(){ 
	parent::setUp();
	}

  public function tearDown(){
	parent::tearDown();
	}
  
  public function test_php_function_exists() {
		$test = function_exists( 'print_r' );
		$this->assertTrue(  $test );
  }  

  public function test_MediaWiki_function_exists() {
		$test = function_exists( 'wfMessage' );
		$this->assertTrue(  $test );
  }  

  public function test_special_page_runs() {
		$s = new SpecialReservation();
		$s->execute( null );
		$o = $s->getOutput();
//		$this->assertTrue(  $o );
		$this->assertFalse(  empty($o->mPagetitle) );
  }  

  public function test_special_page_log_runs() {
		$s = new SpecialReservationLog();
		$s->execute( null );
		$o = $s->getOutput();
		$this->assertFalse(  empty($o->mPagetitle) );
  }  
}
