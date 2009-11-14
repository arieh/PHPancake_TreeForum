<?php
require_once dirname(__FILE__) . "/../classes/PancakeTF_ShusterTestCase.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_Message.class.php";
require_once dirname(__FILE__) . "/../classes/PancakeTF_PDOAccess.class.php";

class PancakeTF_Message_CreateTest extends PancakeTF_ShusterTestCase{
	private $message = null;
	public function setUp(){}
	
	public function setMessage($db=false,$permission=true){
		if ($db){
			$this->setUpDB();
		}else{
			$this->db = $this->getMock('PancakeTF_PDOAccess',array('queryRow','queryArray','count','update','getLastId'));
		}
		
		$p_h = $this->getMock('PancakeTF_PermissionHandlerI',array('doesHavePermission'));
		$p_h->expects($this->any())->method('doesHavePermission')->will($this->returnValue($permission));
		$this->message = new PancakeTF_Message($this->db,$p_h);
	}
	
	public function getMockMessage(){
		$msg = $this->getMock('PancakeTF_Message',array('getId','getForumId','getDna','getBaseId'));
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
		$this->message->setForumId(1);
		$this->message->save();
	}
	
	public function testCreateBaseMessage(){
		$this->setMessage(true);
		$this->message->setTitle('New Message');
		$this->message->setContent('Message Content');
		$this->message->setForumId(1);
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
		$this->message = new PancakeTF_Message($this->db,$p_h,false,array(
			'title'=>'new message',
			'content'=>'set up by constructor',
			'forum_id'=>1
		));
		$this->message->save();
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

		$this->assertEquals ($msg['title'],'new message');
		$this->assertEquals ($msg['content'],'set up by constructor');
		$this->assertEquals ($msg['forum_id'],1);
		$this->assertEquals ($msg['base_id'],$this->message->getId());
	}
}