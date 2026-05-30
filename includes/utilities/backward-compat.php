<?php
/**
 * Prevent backward incompatibilities.
 * @since 1.3.14-beta
 *
 * @package ReallySimpleCMS
 */

// Standardizing system config constants
backCompatV1_3_10_beta();

// Adding legacy constants and global vars
backCompatV1_3_14_beta();

// Adding additional legacy constants and global vars
backCompatV1_3_16_beta();

/**
 * Standardize system config constants.
 * @since 1.3.10-beta
 */
function backCompatV1_3_10_beta(): void {
	$version = '1.3.10-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		$config_file = file(RS_CONFIG);
		$has_charset = $has_collate = false;
		
		$match_charset = preg_grep('/DB_CHARSET/', $config_file);
		if(!empty($match_charset)) $has_charset = true;
		
		$match_collate = preg_grep('/DB_COLLATE/', $config_file);
		if(!empty($match_collate)) $has_collate = true;
		
		if(!$has_charset) {
			foreach($config_file as $line_num => $line) {
				// Skip over unmatched lines
				if(!preg_match('/^define\(\s*\'([A-Z_]+)\',\s+\'([a-z0-9_]+)\'/', $line, $match))
					continue;
				
				$constant = $match[1];
				$value = $match[2];
				
				switch($constant) {
					case 'DB_CHAR':
						$config_file[$line_num] = "define('DB_CHARSET', '" .
							$value . "');" . chr(10);
						
						if(!$has_collate) {
							$collate = array(
								"" . chr(10),
								"// Database collation" . chr(10),
								"define('DB_COLLATE', '');" . chr(10)
							);
							$collate = array_reverse($collate);
							
							foreach($collate as $col)
								array_splice($config_file, $line_num + 1, 0, $col);
						}
						break;
				}
			}
			
			unset($line);
			
			$handle = fopen(RS_CONFIG, 'w');
			
			if($handle !== false) {
				foreach($config_file as $line) fwrite($handle, $line);
				
				fclose($handle);
			}
		}
	}
}

/**
 * Add legacy constants and global vars.
 * @since 1.3.14-beta
 */
function backCompatV1_3_14_beta(): void {
	global $rs_session, $rs_post_types, $rs_taxonomies;
	
	$version = '1.3.14-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		// Current system version
		define('CMS_VERSION', RS_VERSION);
		
		// Path to the database configuration file
		define('DB_CONFIG', RS_CONFIG);
		
		// Path to the database schema file
		define('DB_SCHEMA', RS_SCHEMA);
		
		// Path to the debugging functions file
		define('DEBUG_FUNC', RS_DEBUG_FUNC);
		
		// Path to the critical functions file
		define('CRIT_FUNC', RS_CRIT_FUNC);
		
		// Path to the front end functions file
		define('FUNC', RS_FRONT_FUNC);
		
		// Path to the admin functions file
		define('ADMIN_FUNC', RS_ADMIN_FUNC);
		
		// The name of the CMS engine
		define('CMS_ENGINE', RS_ENGINE);
		
		// User session data
		$session = $rs_session;
		
		// All registered post types (only defaults)
		$post_types = $rs_post_types;
		
		// All registered taxonomies (only defaults)
		$taxonomies = $rs_taxonomies;
	}
}

/**
 * Add additional legacy constants and global vars.
 * @since 1.3.16-beta
 */
function backCompatV1_3_16_beta(): void {
	$version = '1.3.16-beta_dev';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		// Path to the global functions file
		define('GLOBAL_FUNC', RS_GLOBAL_FUNC);
		
		// Path to the front end functions file
		define('RS_FUNC', RS_FRONT_FUNC);
	}
}