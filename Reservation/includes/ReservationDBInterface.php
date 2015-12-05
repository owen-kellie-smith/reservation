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
 * ReservationDBInterface is a class used to access the underlying data.
 *
 * It assumes you have stored your data in the wiki database.
 * It's not as flexible as it looks, though, becasue the accessing select queries assume MySQL.
 */
class ReservationDBInterface {

	public function __construct(){
		;
	}

	public function insert( $table, $a,
		$fname = __METHOD__, $options=array() ){
		$dbr = wfGetDB( DB_MASTER );
		return $dbr->insert( 	  	$table,
		  	$a,
		  	$fname,
		  	$options 
		);
	}

	public function select( $table, $vars, 
		$conds='', $fname = __METHOD__, 
		$options=array(), $join_conds=array() ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select 	( 	  	$table,
		  	$vars,
		  	$conds,
		  	$fname,
		  	$options,
		  	$join_conds 
		); 
		for ($i = 0, $ii = $res->numRows(); $i < $ii; $i++){
			$ret[]=$res->fetchRow();
		}		
		return $ret;
	}

	public function update( $table, $values, $conds, 
		$fname = __METHOD__, $options = array() ){
		$dbr = wfGetDB( DB_MASTER );
		return $dbr->update 	( 	  	$table,
		  	$values,
		  	$conds,
		  	$fname,
		  	$options 
		); 		
	}

	public function delete( $table, $conds, $fname = __METHOD__ ){
		$dbr = wfGetDB( DB_MASTER );
		return $dbr->delete 	( 	  	$table,
		  	$conds,
		  	$fname 
		); 		
	}

}


