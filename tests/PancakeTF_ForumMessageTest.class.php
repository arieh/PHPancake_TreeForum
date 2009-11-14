<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_ForumMessage.class.php";

class PancakeTF_ForumMessageTester extends PancakeTF_ForumMessage{
	public function __call($name,$params){
		if (substr($name,0,7)==='public_'){		
			$name = substr($name,7);
			if (!method_exists($this,$name)){
				throw new Exception("Method $name does not exist for this class");
			}
			return call_user_func_array(array($this,$name),$params);
		}
		if (method_exists(parent,'__call')) parent::__call($name,$params);
	}
}

class PancakeTF_ForumMessageTest extends PancakeTF_ShusterTestCase{
	public function setUp(){
		$this->setUpDB();
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$this->forum = new PancakeTF_ForumMessage($this->db,$p_h,1);
	}
	
	public function testGetMessages(){
		$this->assertEquals(5,count($this->forum->getMessages()));
	}
	
	public function testOrder(){
		$arr = array(1,2,4,3,7);
		$this->assertEquals(array_keys($this->forum->getMessages()),$arr);
	}
}
