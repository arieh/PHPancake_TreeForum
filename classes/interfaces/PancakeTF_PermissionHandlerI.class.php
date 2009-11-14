<?php

interface PancakeTF_PermissionHandlerI {
	/**
	 * checks if the action is allowed for this session
	 * 	@param string $type permission type to check for
	 * 	@param int $forum a forum id
	 * 	@param int $message a message id
	 * @access public
	 * @return bool
	 */
	public function doesHavePermission($type, $forum = false, $message = false);
}