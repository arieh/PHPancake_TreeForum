<?php

interface PancakeTF_ForumI {
	/**
	 * returns the forum`s messages, ordered by their viewing order
	 * @access public
	 * @return array
	 */
	public function getMessages();
}