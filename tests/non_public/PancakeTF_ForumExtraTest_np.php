<?php
require_once dirname(__FILE__) . "/../../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../../classes/PancakeTF_ForumExtra.class.php";

class PancakeTF_ForumExtraTester extends PancakeTF_ForumExtra{
	public function __call($name,$params){
		if (substr($name,0,7)==='public_'){		
			$name = substr($name,7);
			if (method_exists($this,$name)){
				return call_user_func_array(array($this,$name),$params);
			}
		}
		$parents = class_parents($this);
		if (is_array($parents)){
			foreach ($parents as $classname){
				if (method_exists ($classname,'__call')) return parent::__call($name,$params);
			}
		}
		throw new Exception("Method $name does not exist for this class");
	}
}

class PancakeTF_ForumExtraTest_np extends PancakeTF_ShusterTestCase{
	public function setUp(){
		$this->setUpDB();
		$options = array('dba'=>$this->db);
		$this->forum = new PancakeTF_ForumExtraTester(1,$options);
	}
	
	public function testRetrieveBaseMessages(){
		$arr = array(array('id'=>1),array('id'=>5),array('id'=>9));
		$temp = array();
		foreach ($this->forum->public_retrieveBaseMessages() as $message) $temp[]= $message;
		$this->assertEquals($arr,$temp);
	}
	
	
	public function testOrderMessages(){
		$arr = array(
			'1.4'=>array('id'=>4,'dna'=>'1.4','wanted_depth'=>2),
			'1'=>array('id'=>1,'dna'=>'1','wanted_depth'=>1),
			'1.2.3'=>array('id'=>3,'dna'=>'1.2.3','wanted_depth'=>3),
			'1.2'=>array('id'=>2,'dna'=>'1.2','wanted_depth'=>2)			
		);
		$wanted = array(1,2,3,4);
		$new_arr = $this->forum->public_orderMessages($arr);
		$count = 0;
		foreach ($new_arr as $msg){
			$this->assertEquals($msg['id'],$wanted[$count++]);
			$this->assertEquals($msg['wanted_depth'],$msg['depth']);
		} 
	}
}