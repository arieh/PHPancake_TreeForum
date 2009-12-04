<?php
require_once dirname(__FILE__) . "/../../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../../classes/PancakeTF_Forum.class.php";

class ForumTester extends PancakeTF_Forum{
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

class PancakeTF_ForumTest_np extends PancakeTF_ShusterTestCase{
	public function setUp(){
		$this->setUpDB();
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$this->forum = new ForumTester($this->db,$p_h,1);
	}
	
	public function testRetrieveBaseMessages(){
		$arr = array(array('id'=>1),array('id'=>5),array('id'=>9));
		$temp = array();
		foreach ($this->forum->public_retrieveBaseMessages() as $message) $temp[]= $message;
		$this->assertEquals($arr,$temp);
	}
	
	public function testRetrieveSubMessages(){
		$wanted_ids = array(1,2,3,4,7);
		$count = 0;
		foreach($this->forum->public_retrieveSubMessages(1) as $msg) $this->assertEquals($msg['id'],$wanted_ids[$count++]); 
		
		$wanted_ids = array(5,8);
		$count = 0;
		foreach($this->forum->public_retrieveSubMessages(5) as $msg) $this->assertEquals($msg['id'],$wanted_ids[$count++]); 
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