<?php
/**
 * Admin class used to implement the Notice object.
 * @since 1.3.8[b]
 *
 * Notices provide the end user with information related to the status of the page they're currently viewing.
 * Notices can be dismissed (hidden) and shown again.
 */
class Notice {
	/**
	 * The current notice's id.
	 * @since 1.3.8[b]
	 *
	 * @access private
	 * @var string
	 */
	private $id;
	
	/**
	 * Create a notice message.
	 * @since 1.3.8[b]
	 *
	 * @access public
	 * @param string $text -- The notice's text.
	 * @param int $status (optional) -- The notice's status.
	 * @param bool $can_dismiss (optional) -- Whether the notice is dismissible.
	 * @param bool $is_exit (optional) -- Whether the notice is for an exit status (i.e., for a form submission).
	 * @return string
	 */
	public function msg(
		string $text,
		int $status = 2,
		bool $can_dismiss = true,
		bool $is_exit = false
	): string {
		global $notices;
		
		if(!$is_exit) {
			$this->id = md5(strip_tags($text));
			
			if(!in_array($this->id, $notices, true)) $notices[] = $this->id;
		}
		
		if(!is_null($this->defaultMsg($text))) $text = $this->defaultMsg($text);
		
		/**
		 * Possible statuses:
		 * 2 = information (default)
		 * 1 = success
		 * 0 = warning
		 * -1 = failure/error
		 */
		switch($status) {
			case 2:
				$sclass = '';
				$icon = 'fa-solid fa-circle-info';
				break;
			case 1:
				$sclass = 'success';
				$icon = 'fa-solid fa-check';
				break;
			case 0:
				$sclass = 'warning';
				$icon = 'fa-solid fa-triangle-exclamation';
				break;
			case -1:
				$sclass = 'failure';
				$icon = 'fa-solid fa-skull-crossbones';
				break;
			default:
				return '';
		}
		
		$message = tag('span', array(
			'class' => 'icon',
			'content' => tag('i', array(
				'class' => $icon
			))
		)) . ' ' . tag('span', array(
				'class' => 'text',
				'content' => $text
		));
		
		return tag('div', array(
			'class' => 'notice ' . $sclass,
			'content' => $message . ($can_dismiss ? tag('span', array(
				'class' => 'dismiss',
				'content' => tag('i', array(
					'class' => 'fa-solid fa-xmark',
					'title' => 'Dismiss'
				))
			)) : ''),
			'data-id' => $this->id
		));
	}
	
	/**
	 * Unhide all dismissed notices.
	 * @since 1.3.8[b]
	 *
	 * @access public
	 * @param int $user_id -- The user's id.
	 */
	public function unhide(int $user_id): void {
		global $rs_query;
		
		$rs_query->update('usermeta', array('value' => ''), array(
			'user' => $user_id,
			'_key' => 'dismissed_notices'
		));
	}
	
	/**
	 * Check whether a notice has been dismissed.
	 * @since 1.3.8[b]
	 *
	 * @access public
	 * @param string $text -- The notice's text.
	 * @param array $dismissed -- The dismissed notices.
	 * @return bool
	 */
	public function isDismissed(string $text, array $dismissed): bool {
		return in_array(md5(strip_tags($text)), $dismissed, true);
	}
	
	/**
	 * Output a default, predefined message.
	 * @since 1.3.8[b]
	 *
	 * @access private
	 * @param string $msg_code -- The message's "code" abbreviation.
	 * @return null|string
	 */
	private function defaultMsg(string $msg_code): ?string {
		/**
		 * The current defaults are:
		 * ERR - for unexpected errors outside the user's control
		 * REQ - for required form fields
		 */
		switch(strtoupper($msg_code)) {
			case 'ERR':
				$text = 'An unexpected error occurred. Please contact the system administrator.';
				break;
			case 'REQ':
				$text = 'Required fields cannot be left blank!';
				break;
		}
		
		return $text ?? null;
	}
}