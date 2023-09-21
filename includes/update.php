<?php
/**
 * Update the CMS.
 * @since 1.1.0[b]{ss-01}
 */

// Database updater
require_once PATH . INC . '/update-db.php';

// Updating config constants
if(version_compare(CMS_VERSION, '1.3.10', '>=')) {
	$config_file = file(DB_CONFIG);
	$has_collate = false;
	
	$match = preg_grep('/DB_COLLATE/', $config_file);
	
	if(!empty($match)) $has_collate = true;
	
	unset($match);
	
	foreach($config_file as $line_num => $line) {
		// Skip over unmatched lines
		if(!preg_match('/^define\(\s*\'([A-Z_]+)\',\s+\'([a-z0-9_]+)\'/', $line, $match)) continue;
		
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
	
	// Open the file stream
	$handle = fopen(DB_CONFIG, 'w');
	
	// Write to the file
	if($handle !== false) {
		foreach($config_file as $line) fwrite($handle, $line);
		
		fclose($handle);
	}
}