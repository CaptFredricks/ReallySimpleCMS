<?php
/**
 * Link to the ReallySimpleCMS API.
 * @since 1.4.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 */
namespace Engine;

class ApiFetch extends CurlFetch {
	/**
	 * The API endpoint URL.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access private
	 * @var string
	 */
	private $endpoint = 'https://rscms.jacefincham.com/api/';
	
	/**
	 * Class constructor.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct($this->endpoint);
	}
	
	/**
	 * Fetch the CMS version.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access public
	 */
	public function getVersion(): string {
		$params = array(
			'action' => 'version'
		);
		
		return $this->curlGet($params);
	}
}