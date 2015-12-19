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
 * @class ReservationBeneficiary 
 *
 */
class ReservationBeneficiary extends ReservationObject {

	private $user;
	private $lagInSeconds = 60;

	public function __construct( User $user){
		$this->user = $user;
	}

	public function getName(){
		return  $this->user->getName();
	}

	public function getRights(){
		return $this->user->getRights();
	}

	public function getGroups(){
		return $this->user->getGroups();
	}

	public function getMessages(){
		$res = array();
		$db = new ReservationDBInterface();
		$vars = array(
			'res_message_text',
			'res_message_type',
			);
		$res = $db->select(
			array('res_message'), 
			$vars,
			array(
				'res_message_user_id'=>$this->user->getID(),
				'res_message_time > DATE_SUB( now(), INTERVAL ' . $this->lagInSeconds . ' SECOND )',
				)
			);
		$ret = array();
		if ( count ( $res ) > 0 ){
			foreach ( $res AS $row ){
				$r['type'] = $row['res_message_type'];
				$r['message'] = $row['res_message_text'];
				$ret[] = $r;
				$r = null;
			}
		}
		return $ret;
	}

	public function getResourceRightList(){
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
				'res_group_right'=>$this->getRights(),
				)
			);
		return $res;
	}


	public function getOverridingResources( $groupID=-999 ){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_id',
			);
		$res = $db->select(
			array('res_resource'), 
			$vars,
			array(
				'res_resource_group_id'=>$groupID,
				)
			);
		return $res;
	}

	private function getDisallowedGroupIDs(){
		$all = $this->getAllGroupIDs();
		$mine = $this->getAllowableGroupIDs();
		$res = array_diff($all, $mine);
		return $res;
	}

	public function getDisallowedGroups(){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_group_id',
			'res_group_name',
			);
		$res = $db->select(
			array('res_group'), 
			$vars,
			array(
				'res_group_id'=>$this->getDisallowedGroupIDs(),
				)
			);
		$r = array();
		if (count($res)>0){
			foreach ($res AS $row){
				$r[$row['res_group_id']]=$row['res_group_name'];
			}
		}
//echo __FILE__ . "getDisallowedGroups " . print_r($res,1);
//echo __FILE__ . "getDisallowedGroups " . print_r($r,1);
		return $r;
	}

	private function getAllGroupIDs(){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_group_id',
			);
		$res = $db->select(
			array('res_group'), 
			$vars
			);
		$r = array();
		if (count($res)>0){
			foreach ($res AS $row){
				$r[] = $row['res_group_id'];
			}
		}
		return $r;
	}

	private function getAllowableGroupIDs(){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_group_id',
			);
		$res = $db->select(
			array('res_group'), 
			$vars,
			array(
				'res_group_right'=>$this->getRights(),
				)
			);
		$r = array();
		if (count($res)>0){
			foreach ($res AS $row){
				$r[] = $row['res_group_id'];
			}
		}
		return $r;
	}

	public function getAllowableResources(){
		$db = new ReservationDBInterface();
		$vars = array(
			'res_resource_name',
			'res_resource_id',
			);
		$res = $db->select(
			array('res_group','res_resource'), 
			$vars,
			array(
				'res_resource_group_id=res_group_id',
				'res_group_right'=>$this->getRights(),
				)
			);
		return $res;
	}

} // end of class

