<?php
/**
 * Link to an API via cURL.
 * @since 1.4.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 */
namespace Engine;

class CurlFetch {
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
	 * @param string $url -- URL to request.
	 */
	public function __construct(string $endpoint) {
		$this->endpoint = $endpoint;
	}
	
	/**
	 * Send a GET requst using cURL.
	 * @since 1.4.0-beta_snap-01
	 *
	 * @access protected
	 * @param array $get -- GET values to send.
	 * @param array $options -- cURL options.
	 * @return string|int
	 */
	protected function curlGet(array $get = array(), array $options = array()): string|int {
		$defaults = array(
			CURLOPT_URL => $this->endpoint . (
				!empty($get) ? ((strpos($this->endpoint, '?') === false ? '?' : '') . http_build_query($get)) : ''
			),
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => true,
			# CURLOPT_TIMEOUT => 4,
			# CURLOPT_SSL_VERIFYHOST => false,
			# CURLOPT_SSL_VERIFYPEER => false
		);
		
		try {
			$ch = curl_init();
			curl_setopt_array($ch, ($options + $defaults));
			
			if($ch === false)
				throw new Exception('failed to initialize');
			
			$content = curl_exec($ch);
			
			// Check the return value of curl_exec(), too
			if($content === false)
				throw new Exception(curl_error($ch), curl_errno($ch));
			
			// Check HTTP return code, too; might be something else than 200
			$httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			return $content;
		} catch(Exception $e) {
			trigger_error(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()
			), E_USER_ERROR);
			return -1;
		} finally {
			// Close curl handle unless it failed to initialize
			if(is_resource($ch)) curl_close($ch);
		}
	}
}