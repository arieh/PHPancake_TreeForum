<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_TestCase.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_MessageExtra.class.php";

class PancakeTF_MessageExtraTester extends PancakeTF_MessageExtra{
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

class PancakeTF_MessageExtraTest extends PancakeTF_TestCase{
	public function setMessage($db=false,$permission=true){
		if ($db){
			$this->setUpDB();
		}else{
			$this->db = $this->getMock('PancakeTF_PDOAccess',array('queryRow','queryArray','count','update','getLastId'));
		}
		
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue($permission));
		$this->message = new PancakeTF_MessageExtraTester($this->db,$p_h);
	}
	
	public function getMockMessage(){
		$msg = $this->getMock('PancakeTF_MessageExtra',array('getId','getForumId','getDna','getBaseId'));
		return $msg;
	}
	
	public function testCreation(){
		$this->setMessage();
		$this->assertTrue($this->message instanceof PancakeTF_MessageI);
	}
	
	public function testForumIdAssignment(){
		$this->setMessage();
		$this->db->expects($this->once())->method('count')->will($this->returnValue(1));
		$this->message->setForumId(1);
		$this->assertEquals($this->message->getForumId(),1);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidForumIdAssignment(){
		$this->setMessage();
		$this->db->expects($this->once())->method('count')->will($this->returnValue(0));
		$this->message->setForumId(200);
	}
	
	public function testTitleAssignment(){
		$this->setMessage();
		$this->message->setTitle('A new message');
		$this->assertEquals($this->message->getTitle(),'A new message');
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidTitleAssignment(){
		$this->setMessage();
		$this->message->setTitle(array('aaaa'));
	}
	
	public function testSetContent(){
		$this->setMessage();
		$this->message->setContent('1234');
		$this->assertEquals($this->message->getContent(),'1234');
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInavlidSetContent(){
		$this->setMessage();
		$this->message->setContent(array('aaa'));
	}
	
	public function testSetParentNoForumId(){
		$this->setMessage(true);
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(2))
			->method('getForumId')->will($this->returnValue(1));
		$pr->expects($this->once())
			->method('getId')->will($this->returnValue(1));
		$this->message->setParent($pr);
	}
	
	public function testSetParentWithForumId(){
		$this->setMessage(true);
		$this->message->setForumId(1);
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(3))
			->method('getForumId')->will($this->returnValue(1));
		$pr->expects($this->once())
			->method('getId')->will($this->returnValue(1));
		$this->message->setParent($pr);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetParentInvalidForum(){
		$this->setMessage(true);
		$this->message->setForumId(1);
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(1))
			->method('getForumId')->will($this->returnValue(20));
		$this->message->setParent($pr);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetParentWrongForum(){
		$this->setMessage(true);
		$this->message->setForumId(1);
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(2))
			->method('getForumId')->will($this->returnValue(2));
		$pr->expects($this->once())
			->method('getId')->will($this->returnValue(1));
		$this->message->setParent($pr);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */	
	public function testSetParentBadMessageID(){
		$this->setMessage(true);
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(1))
			->method('getForumId')->will($this->returnValue(1));
		$pr->expects($this->once())
			->method('getId')->will($this->returnValue(100));
		$this->message->setParent($pr);
	}
	
	/**
	 * @expectedException LogicException
	 */
	public function testGetDnaNoMessage(){
		$this->setMessage();
		$this->message->getDna();
	}
	
	/**
	 * @expectedException LogicException
	 */
	public function testIsBaseNoMessage(){
	 	$this->setMessage();
	 	$this->message->isBase();
	 }
	 
	/**
	 * @expectedException LogicException
	 */
	public function testGetBaseIdNoMessage(){
	 	$this->setMessage();
	 	$this->message->getBaseId();
	 }

	public function testCreateMessageWithParent(){
		$this->setMessage(true);
		$this->message->setTitle('New Message');
		$this->message->setForumId(1);
		
		$pr = $this->getMockMessage();
		$pr->expects($this->exactly(3))
			->method('getForumId')->will($this->returnValue(1));
		$pr->expects($this->exactly(1))
			->method('getId')->will($this->returnValue(2));
		$pr->expects($this->any())->method('getDna')->will($this->returnValue('1.2'));
		$pr->expects($this->any())->method('getBaseId')->will($this->returnValue('1'));
		$this->message->setParent($pr);
		$this->message->setUserId(1);
		$this->message->save();
		$this->assertTrue($this->message->getId()>0);
		$sql = "SELECT `dna`,`base_id` FROM `pancaketf_messages` WHERE `id`=?";
		$row = $this->db->queryRow($sql,array($this->message->getId()));
		$this->assertEquals($row['dna'],'1.2.10');
		$this->assertEquals($row['base_id'],1);
	}
	
	/**
	 * @expectedException PancakeTF_NoPermissionException
	 */
	public function testCreateMessageNoPermission(){
		$this->setMessage(false,false);
		$this->message->setTitle('New Message');
		$this->message->setUserId(1);
		$this->message->setForumId(1);
		$this->message->save();
	}
	
	public function testCreateBaseMessage(){
		$this->setMessage(true);
		$this->message->setTitle('New Message');
		$this->message->setContent('Message Content');
		$this->message->setForumId(1);
		$this->message->setUserId(1);
		$this->message->save();
		$this->assertTrue($this->message->getId()>0);
		$sql = "
				SELECT
					pancaketf_messages.id,
					pancaketf_messages.forum_id,
					pancaketf_messages.dna,
					pancaketf_messages.base_id,
					pancaketf_message_contents.title,
					pancaketf_message_contents.content
				FROM
					pancaketf_messages
				Inner Join pancaketf_message_contents ON pancaketf_message_contents.message_id = pancaketf_messages.id 
				WHERE `id`=?";
		$msg = $this->db->queryRow($sql,array($this->message->getId()));
		$this->assertEquals ($msg['title'],'New Message');
		$this->assertEquals ($msg['content'],'Message Content');
		$this->assertEquals ($msg['forum_id'],1);
		$this->assertEquals ($msg['base_id'],$this->message->getId());
	}
	
	public function testSetByConstruction(){
		$this->setUpDB();
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue(true));
		$this->message = new PancakeTF_MessageExtra($this->db,$p_h,false,array(
			'title'=>'new message',
			'content'=>'set up by constructor',
			'forum_id'=>1,
			'user'=>1
		));
		$this->message->save();
		$sql = "
				SELECT
					pancaketf_messages.id,
					pancaketf_messages.forum_id,
					pancaketf_messages.dna,
					pancaketf_messages.base_id,
					pancaketf_messages.`date`,
					pancaketf_message_contents.title,
					pancaketf_message_contents.content,
					pancaketf_message_extras.`user`,
					pancaketf_message_extras.votes
				FROM
					pancaketf_messages
				Inner Join pancaketf_message_contents ON pancaketf_message_contents.message_id = pancaketf_messages.id
				Inner Join pancaketf_message_extras ON pancaketf_message_extras.message_id = pancaketf_messages.id
				WHERE `id`=?";
		$msg = $this->db->queryRow($sql,array($this->message->getId()));

		$this->assertEquals ($msg['title'],'new message');
		$this->assertEquals ($msg['content'],'set up by constructor');
		$this->assertEquals ($msg['forum_id'],1);
		$this->assertEquals ($msg['base_id'],$this->message->getId());
		$this->assertEquals ($msg['user'],1);
		$this->assertEquals ($msg['votes'],0);
	}
	
	/**
	 * @expectedException LogicException
	 */
	public function testSaveWithoutUserId(){
		$this->setMessage(true);
		$this->message->setTitle('New Message');
		$this->message->setContent('Message Content');
		$this->message->setForumId(1);
		$this->message->save();
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetInvalidUserIdMock(){
		$this->setMessage();
		$this->db->expects($this->once())->method('count')->will($this->returnValue(0));
		$this->message->setUserId(3);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetInvalidUserId(){
		$this->setMessage(true);
		$this->message->setUserId(30);
	}
	
	public function testIncreaseVote(){
		$this->setMessage();
		$this->assertEquals($this->message->getVotes(),0);
		$this->message->increaseVote();
		$this->assertEquals($this->message->getVotes(),1);
		$this->message->increaseVote();
		$this->assertEquals($this->message->getVotes(),2);
	}
	
	public function testDencreaseVote(){
		$this->setMessage();
		$this->assertEquals($this->message->getVotes(),0);
		$this->message->decreaseVote();
		$this->assertEquals($this->message->getVotes(),-1);
		$this->message->decreaseVote();
		$this->assertEquals($this->message->getVotes(),-2);
	}
	
	public function testExtraMessageRetrival(){
		$this->setMessage(true);
		$this->message->setId(3);
		$this->assertEquals($this->message->getVotes(),2);
		$this->assertEquals($this->message->getUserData(),array('id'=>2,'name'=>'ita','email'=>'some_email@gmail.com'));
	}
	
	public function testDeleteFlag(){
		$this->setMessage();
		$this->assertFalse($this->message->getDeleteFlag());
	}
	
	public function testSetDeleteFlagNoDB(){
		$this->setMessage();
		$this->assertTrue($this->message->setDeleteFlag(true)->getDeleteFlag());
	}
	
	public function testSetDeleteFlag(){
		$this->setMessage(true);
		$this->message->setId(2);
		$this->message->setDeleteFlag(true);
		$this->message->save();
		$this->assertEquals($this->db->count('pancaketf_message_extras',array('message_id'=>2,'delete_flag'=>1)),1);
	}
}
