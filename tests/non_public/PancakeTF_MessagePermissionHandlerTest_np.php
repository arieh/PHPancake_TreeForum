<?php
require_once dirname(__FILE__) . "/../../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../../classes/PancakeTF_MessagePermissionHandler.class.php";

class PancakeTF_MessagePermissionHandlerTester extends PancakeTF_MessagePermissionHandler{
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

class PancakeTF_MessagePermissionHandlerTest_np extends PancakeTF_ShusterTestCase{
	public function setUp(){
		$this->setUpDB();
		$this->tested = new PancakeTF_MessagePermissionHandlerTester($this->db);
	}
	
	public function testIsMessaageOwner(){
		$this->assertTrue($this->tested->public_isMessageOwner(1,1));
		$this->assertFalse($this->tested->public_isMessageOwner(2,2));
	}
}
