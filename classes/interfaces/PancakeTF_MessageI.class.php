<?php

interface PancakeTF_MessageI {
	/**
	 * returns the message's id
	 * @access public
	 * @return int
	 */
	public function getId();
	
	/**
	 * returns the Message`s forum-id
	 * @access public
	 * @return int
	 */
	public function getForumId();
	
	/**
	 * returns the Message`s Title
	 * @access public
	 * @return string
	 */
	public function getTitle();
	
	/**
	 * returns the Message's content
	 * @access public
	 * @return string
	 */
	public function getContent();
	
	/**
	 * returns the Message`s base-id
	 * @access public
	 * @return int a message id
	 * 
	 * @throws LogicException
	 * 
	 */
	public function getBaseId();
	
	/**
	 * return the Message`s DNA
	 * @access public
	 * @return string
	 * 
	 * @throws LogicException
	 */
	public function getDna();
	
	/**
	 * returns wether or not the message is a base message
	 * @access public
	 * @return bool 
	 * 
	 * @throws LogicException
	 */
	public function isBase();
	
	/**
	 * returns the last time the message was updated
	 * @access public
	 * @return string
	 * 
	 * @throws LogicException if message was not yet initialized
	 */
	public function getDate();
	
	/**
	 * sets the message as a base message
	 * @access public
	 * @return this
	 */
	public function setBase();
	/**
	 * set's/changes the Message`s title
	 * 	@param string $value the value of the new title
	 * @access public
	 * @return PancakeTF_MessageI the current message
	 * 
	 * @throws InvalidArgumentException if title is invalid
	 */
	public function setTitle($value);
	
	/**
	 * sets/changes the Message`s content
	 * 	@param string $value the new content
	 * @access public
	 * @return PancakeTF_MessageI the current message
	 */
	public function setContent($value);
	
	/**
	 * sets the message`s id (can be used to open a Message). 
	 * 	@param int $id a valid message id
	 * @access public 
	 * @return PancakeTF_MessageI the current message
	 */
	public function setId( $id); 
	
	/**
	 * sets/changes the message`s forum id
	 * 	@param int $id the new forum-id. must be a valid forum id
	 * @access public 
	 * @return PancakeTF_MessageI
	 * 
	 * @throws InvalidArgumentException if forum id is invalid
	 */
	public function setForumId( $id);
	
	/**
	 * sets/changes the message`s parent id. must be a valid message id
	 * 
	 * this will actualy change the message`s dna and all it's siblings` dna. The message must be of the same forum.
	 * if the id is set to itself the message will be considered and marked as base (default).
	 * 
	 * 	@param PancakeTF_MessageI $message a new parent message. message must be of the same forum and connot be a descendant of the current message 
	 * @access public 
	 * @return PancakeTF_MessageI 
	 * 
	 * @throws InvalidArgumentException if the parent isn't from the right forum or is a sibling of the current message
	 * @throws PancakeTF_NoPermissionException if the user doesn't have permission to move the message
	 */
	public function setParent(PancakeTF_MessageI $message);
	
	/**
	 * an accessor to the variouse setters via an array
	 * 	@param array $options an associative array of options and their values
	 * @access public
	 * 
	 * @throws InvalidArgumentException if option is not allowed to be manualy changed
	 */
	public function setOptions($options = array());
	
	/**
	 * saves the changes made (if any) to the Message to the DB
	 * @access public 
	 */
	public function save();
	
	/**
	 * completely deletes a message and its siblings
	 * @access public
	 * @throws PancakeTF_NoPermissionException
	 */
	public function delete();
}