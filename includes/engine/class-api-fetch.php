<?php
/**
 * Link to the ReallySimpleSystems API.
 * @since 1.4.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_api_fetch
 *
 * ## VARIABLES [1] ##
 * - private string $endpoint
 *
 * ## METHODS [4] ##
 * - public __construct(string $endpoint)
 * - public getModules(): string
 * - public getVersion(): string
 * - public getDownload(): string
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
	private $endpoint;
	
	/**
	 * Class constructor.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access public
	 * @param string $endpoint -- The endpoint to query.
	 */
	public function __construct(string $endpoint = 'rscms') {
		$api_base = 'https://api.jacefincham.com/';
		
		$this->endpoint = $api_base . slash($endpoint);
		
		parent::__construct($this->endpoint);
	}
	
	/**
	 * Fetch a list of available modules.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @return string
	 */
	public function getModules(): string {
		return $this->curlGet('');
	}
	
	/**
	 * Fetch the software version.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access public
	 * @return string
	 */
	public function getVersion(): string {
		return $this->curlGet('version');
	}
	
	/**
	 * Fetch the download file.
	 * @since 1.4.0-beta_snap-02
	 *
	 * @access public
	 * @return string
	 */
	public function getDownload(): string {
		return $this->curlGet('download');
	}
}