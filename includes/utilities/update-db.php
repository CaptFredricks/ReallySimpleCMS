<?php
/**
 * This file handles structural changes to the database that would otherwise break the system.
 * The goal is to ensure that no existing data is lost during the update.
 * Old scripts will be removed after at least two major releases.
 * @since 1.3.5-beta
 *
 * @package ReallySimpleCMS
 */

global $rs_query;

// Prefix database tables (this must run before the other scripts to avoid errors)
updateDB__1_3_16_beta();

// Various tweaks
updateDB__1_3_12_beta();

// Add `up_is_default` column to `user_privileges` table
updateDB__1_3_14_beta();

// Add `active_modules` setting and rename `theme` to `active_theme`
updateDB__1_3_15_beta();

/**
 * Various tweaks.
 * @since 1.3.12-beta
 */
function updateDB__1_3_12_beta() {
	global $rs_query;
	
	$version = '1.3.12-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		// Change `unapproved` comments to `pending`
		$comments = $rs_query->select(getTable('c'), 'COUNT(status)', array(
			'status' => 'unapproved'
		));
		
		if($comments > 0) {
			$rs_query->update(getTable('c'), array(
				'status' => 'pending'
			), array(
				'status' => 'unapproved'
			));
		}
		
		// Add `login_slug` setting
		if(getSetting('login_slug') === false) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'login_slug',
				'value' => ''
			));
		}
		
		// Update table schemas and columns
		
		// `postmeta` table
		if($rs_query->columnExists('postmeta', '_key')) {
			$table = getTable('pm');
			$table_name = 'postmeta';
			
			// Add new columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD datakey varchar(255) NOT NULL;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD value_temp longtext NOT NULL;");
			
			$postmeta = $rs_query->select($table, array('id', '_key', 'value'));
			
			// Move the data to the new columns
			foreach($postmeta as $pmeta) {
				$rs_query->update($table, array(
					'datakey' => $pmeta['_key'],
					'value_temp' => $pmeta['value']
				), array(
					'id' => $pmeta['id']
				));
			}
			
			// Replace the old index
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP INDEX _key;");
			$rs_query->doQuery("CREATE INDEX datakey ON `{$table_name}` (datakey);");
			
			// Replace the old columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _key;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN value;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` CHANGE `value_temp` `value` longtext NOT NULL;");
		}
		
		// `usermeta` table
		if($rs_query->columnExists('usermeta', '_key')) {
			$table = getTable('um');
			$table_name = 'usermeta';
			
			// Add new columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD datakey varchar(255) NOT NULL;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD value_temp longtext NOT NULL;");
			
			$usermeta = $rs_query->select($table, array('id', '_key', 'value'));
			
			// Move the data to the new columns
			foreach($usermeta as $umeta) {
				$rs_query->update($table, array(
					'datakey' => $umeta['_key'],
					'value_temp' => $umeta['value']
				), array(
					'id' => $umeta['id']
				));
			}
			
			// Replace the old index
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP INDEX _key;");
			$rs_query->doQuery("CREATE INDEX datakey ON `{$table_name}` (datakey);");
			
			// Replace the old columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _key;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN value;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` CHANGE `value_temp` `value` longtext NOT NULL;");
		}
		
		// `user_roles` table
		if($rs_query->columnExists('user_roles', '_default')) {
			$table = getTable('ur');
			$table_name = 'user_roles';
			
			// Add new column
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD is_default tinyint(1) unsigned NOT NULL default '0';");
			
			$user_roles = $rs_query->select($table, array('id', '_default'));
			
			// Move the data to the new column
			foreach($user_roles as $role) {
				switch($role['_default']) {
					case 'yes':
						$is_default = 1;
						break;
					case 'no':
						$is_default = 0;
						break;
				}
				
				$rs_query->update($table, array(
					'is_default' => $is_default
				), array(
					'id' => $role['id']
				));
			}
			
			// Replace the old column
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _default;");
		}
	}
}

/**
 * Add `up_is_default` column to `user_privileges` table.
 * @since 1.3.14-beta
 */
function updateDB__1_3_14_beta() {
	global $rs_query;
	
	$version = '1.3.14-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		if(!$rs_query->columnExists('user_privileges', 'up_is_default')) {
			$table = getTable('up');
			$table_name = 'user_privileges';
			
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD up_is_default tinyint(1) unsigned NOT NULL default '0';");
			
			$privileges = $rs_query->select($table, 'id', array(), array(
				'order_by' => 'id'
			));
			
			if(!empty($privileges)) {
				foreach($privileges as $privilege) {
					$rs_query->update($table, array(
						'up_is_default' => 1
					), array(
						'id' => array('<=', 49)
					));
				}
			}
		}
	}
}

/**
 * Add `active_modules` setting and rename `theme` to `active_theme`.
 * @since 1.3.15-beta
 */
function updateDB__1_3_15_beta() {
	global $rs_query;
	
	$version = '1.3.15-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		if(getSetting('active_modules') === false) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'active_modules',
				'value' => ''
			));
		}
		
		if(getSetting('theme') !== false) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'active_theme',
				'value' => $active_theme
			));
			
			$rs_query->delete(getTable('s'), array(
				'name' => 'theme'
			));
		}
	}
}

/**
 * Prefix database tables (this must run before the other scripts to avoid errors).
 * @since 1.3.16-beta
 */
function updateDB__1_3_16_beta() {
	global $rs_query;
	
	$version = '1.3.16-beta';
	
	if(version_compare(RS_VERSION, $version, '>=')) {
		$schema = dbSchema();
		
		$px = array(
			'posts' => 'p_',
			'postmeta' => 'pm_',
			'comments' => 'c_',
			'redirects' => 'r_',
			'terms' => 't_',
			'taxonomies' => 'ta_',
			'term_relationships' => 'tr_',
			'users' => 'u_',
			'usermeta' => 'um_',
			'user_roles' => 'ur_',
			'user_privileges' => 'up_',
			'user_relationships' => 'ue_',
			'login_attempts' => 'la_',
			'login_blacklist' => 'lb_',
			'login_rules' => 'lr_',
			'settings' => 's_'
		);
		
		foreach($schema as $key => $value) {
			$prefixed = false;
			
			if($rs_query->columnExists($key, $px[$key] . 'id'))
				$prefixed = true;
			
			// Skip tables that are already updated
			if($prefixed) continue;
			
			$temp_table = $key . '_temp';
			
			if(!$rs_query->tableExists($temp_table)) {
				// Create a temp table
				$rs_query->createTable($temp_table, $value);
				
				$old_data = $rs_query->select($key);
				
				for($i = 0; $i < count($old_data); $i++) {
					foreach($old_data[$i] as $dkey => $dval) {
						// Update columns whose names have changed
						switch($key) {
							case 'posts':
							case 'comments':
								if($dkey === 'date') {
									$dkey = 'created';
									$old_data[$i][$dkey] = $dval;
									unset($old_data[$i]['date']);
								}
								break;
							case 'users':
								if($dkey === 'security_key') {
									$dkey = 'token';
									$old_data[$i][$dkey] = $dval;
									unset($old_data[$i]['security_key']);
								}
								break;
							case 'postmeta':
							case 'usermeta':
								if($dkey === 'datakey') {
									$dkey = 'key';
									$old_data[$i][$dkey] = $dval;
									unset($old_data[$i]['datakey']);
								}
								break;
						}
						
						// Prefix all of the old keys
						$old_data[$i][$px[$key] . $dkey] = $old_data[$i][$dkey];
						unset($old_data[$i][$dkey]);
					}
					
					// Move the data to the temp table
					$rs_query->insert($temp_table, $old_data[$i]);
				}
				
				// Delete the old table and rename the temp one
				$rs_query->dropTable($key);
				$rs_query->doQuery("ALTER TABLE `" . $temp_table . "` RENAME TO `" . $key . "`;");
			}
		}
		
		// Add `comments_per_page` setting
		if(getSetting('comments_per_page') === false) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'comments_per_page',
				'value' => 10
			));
		}
	}
}