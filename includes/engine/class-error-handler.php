<?php
/**
 * Core class used to implement the ErrorHandler object.
 * All application-level errors are handled by this class, overriding PHP defaults.
 * @since 1.3.14-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_error_handler
 *
 * ## VARIABLES [2] ##
 * - private array $backtrace
 * - private array $error
 *
 * ## METHODS [6] ##
 * { ERROR HANDLING [6] }
 * - public logError(object $exception): void
 * - public triggerError(): void
 * - public generateError(): void
 * - public generateDeprecation(): void
 * - private errorHandler(int $type, string $message): bool
 * - private getErrorType(): int
 */
namespace Engine;

class ErrorHandler {
	/**
	 * The code backtrace.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $backtrace = array();
	
	/**
	 * The generated error.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @var array
	 */
	private $error = array();
	
	/*------------------------------------*\
		ERROR HANDLING
	\*------------------------------------*/
	
	/**
	 * Generate an error log.
	 * @since 1.0.1-alpha
	 *
	 * @access public
	 * @param object $exception -- The exception.
	 */
	public function logError(object $exception): void {
		$timestamp = date('[d-M-Y H:i:s T]', time());
		
		error_log($timestamp . ' ' . $exception->getMessage() . chr(10), 3, 'error_log');
	}
	
	/**
	 * Backtrace the problematic code and launch the error page.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function triggerError(): void {
		$this->backtrace = debug_backtrace();
		$this->error = error_get_last();
		
		$critical_error_types = array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
		
		if(isset($this->error['type']) && in_array($this->error['type'], $critical_error_types, true)) {
			global $rs_error;
			
			require_once PATH . UTILS . '/error.php';
		} else {
			if(DEBUG_MODE === true || ini_get('display_errors'))
				$this->generateError();
		}
	}
	
	/**
	 * Display an error message.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function generateError(): void {
		set_error_handler(self::class . '::errorHandler');
		
		if(DEBUG_MODE === true || ini_get('display_errors')) {
			$caller = next($this->backtrace);
			
			$message = $this->error['message'] . '. <b>Stack trace</b>: The <b><i>' . $caller['function'] .
				'</i></b> function in <b>' . $caller['file'] . '</b> on line <b>' . $caller['line'] . '</b>';
			
			if($this->getErrorType() !== -1)
				trigger_error($message, $this->getErrorType());
		} else {
			echo '<p>This site has encountered an unexpected error.</p>';
			echo '<p>Check back again later to see if the issue has been resolved.</p>';
		}
	}
	
	/**
	 * Display a deprecation notice.
	 * @since 1.3.14-beta
	 *
	 * @access public
	 */
	public function generateDeprecation(): void {
		set_error_handler(self::class . '::errorHandler');
		
		if(DEBUG_MODE === true || ini_get('display_errors')) {
			$caller = debug_backtrace()[2];
			
			$message = 'the <i>' . $caller['function'] . '</i> function is deprecated in <b>' . $caller['file'] .
				'</b> on line <b>' . $caller['line'] . '</b>';
			
			trigger_error($message, E_USER_NOTICE);
		}
	}
	
	/**
	 * Create a custom error handler.
	 * @since 1.3.12-beta
	 *
	 * @access public
	 * @param int $type -- The error type.
	 * @param string $message -- The error message.
	 * @return bool
	 */
	public static function errorHandler(int $type, string $message): bool {
		if(!(error_reporting() & $type)) return false;
		
		switch($type) {
			case E_ERROR:
			case E_USER_ERROR:
				echo '<p class="php-error"><b>Error</b>: ' . $message . '</p>';
				exit(1);
			case E_USER_WARNING:
				echo '<p class="php-warning"><b>Warning</b>: ' . $message . '</p>';
				break;
			case E_USER_NOTICE:
				echo '<p class="php-notice"><b>Notice</b>: ' . $message . '</p>';
				break;
		}
		
		return true;
	}
	
	/**
	 * Determine the error type.
	 * @since 1.3.14-beta
	 *
	 * @access private
	 * @return int
	 */
	private function getErrorType(): int {
		$type = $this->error['type'];
		
		if(is_null($type)) return -1;
		
		return match($type) {
			// E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR
			1, 4, 16, 64,
			256, 4096 => E_USER_ERROR,
			// E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING, E_STRICT
			2, 32, 128, 512, 2048 => E_USER_WARNING,
			// E_NOTICE, E_USER_NOTICE
			8, 1024 => E_USER_NOTICE,
			// E_DEPRECATED, E_USER_DEPRECATED
			8192, 16384 => E_USER_DEPRECATED
		};
	}
}