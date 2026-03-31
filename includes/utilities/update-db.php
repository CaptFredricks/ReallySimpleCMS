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

// Prefixing database tables (this must run before the other scripts to avoid errors)
updateDB__1_4_0_beta_snap_02();

// Various tweaks
updateDB__1_3_12_beta();

// Add `is_default` column to `user_privileges` table
updateDB__1_3_14_beta();

// Update privileges
updateDB__1_3_13_beta();

// Add `active_modules` setting and rename `theme` to `active_theme`
updateDB__1_3_15_beta();

// Various tweaks
function updateDB__1_3_12_beta() {
	global $rs_query;
	
	if(version_compare(RS_VERSION, '1.3.12-beta', '>=')) {
		// Changing `unapproved` comments to `pending`
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
		
		// Adding `login_slug` setting
		$settings = $rs_query->selectRow(getTable('s'), 'COUNT(name)', array(
			'name' => 'login_slug'
		));
		
		if($settings === 0) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'login_slug',
				'value' => ''
			));
		}
		
		// Updating table schemas and columns
		
		// `postmeta` table
		if($rs_query->columnExists('postmeta', '_key')) {
			$table = getTable('pm');
			$table_name = 'postmeta';
			
			// Add new columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD pm_key varchar(255) NOT NULL;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD pm_value_temp longtext NOT NULL;");
			
			$postmeta = $rs_query->select($table, array('id', '_key', 'value'));
			
			// Move the data to the new columns
			foreach($postmeta as $pmeta) {
				$rs_query->update($table, array(
					'key' => $pmeta['_key'],
					'value_temp' => $pmeta['pm_value']
				), array(
					'id' => $pmeta['pm_id']
				));
			}
			
			// Replace the old index
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP INDEX _key;");
			$rs_query->doQuery("CREATE INDEX metakey ON `{$table_name}` (pm_key);");
			
			// Replace the old columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _key;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN pm_value;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` CHANGE `pm_value_temp` `pm_value` longtext NOT NULL;");
		}
		
		// `usermeta` table
		if($rs_query->columnExists('usermeta', '_key')) {
			$table = getTable('um');
			$table_name = 'usermeta';
			
			// Add new columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD um_key varchar(255) NOT NULL;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD um_value_temp longtext NOT NULL;");
			
			$usermeta = $rs_query->select($table, array('id', '_key', 'value'));
			
			// Move the data to the new columns
			foreach($usermeta as $umeta) {
				$rs_query->update($table, array(
					'key' => $umeta['_key'],
					'value_temp' => $umeta['um_value']
				), array(
					'id' => $umeta['um_id']
				));
			}
			
			// Replace the old index
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP INDEX _key;");
			$rs_query->doQuery("CREATE INDEX metakey ON `{$table_name}` (um_key);");
			
			// Replace the old columns
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _key;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN um_value;");
			$rs_query->doQuery("ALTER TABLE `{$table_name}` CHANGE `um_value_temp` `um_value` longtext NOT NULL;");
		}
		
		// `user_roles` table
		if($rs_query->columnExists('user_roles', '_default')) {
			$table = getTable('ur');
			$table_name = 'user_roles';
			
			// Add new column
			$rs_query->doQuery("ALTER TABLE `{$table_name}` ADD ur_is_default tinyint(1) unsigned NOT NULL default '0';");
			
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
				
				$rs_query->update(array($table, $px), array(
					'is_default' => $is_default
				), array(
					'id' => $role['ur_id']
				));
			}
			
			// Replace the old column
			$rs_query->doQuery("ALTER TABLE `{$table_name}` DROP COLUMN _default;");
		}
	}
}

// Update privileges
function updateDB__1_3_13_beta() {
	global $rs_query;
	
	if(version_compare(RS_VERSION, '1.3.13-beta', '>=')) {
		$tables = array('user_roles', 'user_relationships', 'user_privileges');
		$px = array('ur_', 'ue_', 'up_');
		$schema = dbSchema();
		
		if($rs_query->selectRow(array($tables[2], $px[2]), 'COUNT(*)', array(
			'name' => 'can_update_core'
		)) === 0) {
			// Check if we have custom roles
			if($rs_query->selectRow(array($tables[0], $px[0]), 'COUNT(*)', array(
				'id' => array('>', 4)
			)) > 0) {
				// Fetch all the non-default data
				$roles = $rs_query->select(array($tables[0], $px[0]), '*', array(
					'id' => array('>', 4)
				));
			} else {
				$roles = array();
			}
			
			// Fetch all the non-default data
			$privileges = $rs_query->select(array($tables[2], $px[2]), '*', array(
				'id' => array('>', 49)
			));
			
			$relationships = $rs_query->select(array($tables[1], $px[1]), '*', array(
				'privilege' => array('>', 49)
			));
			
			// Replace the data
			foreach($tables as $table) {
				$rs_query->dropTable($table);
				$rs_query->createTable($table, $schema[$table]);
				
				populateTable($table);
			}
			
			// Add in the non-default data
			if(!empty($roles)) {
				foreach($roles as $role) {
					$rs_query->insert(array($tables[0], $px[0]), array(
						'name' => $role['ur_name'],
						'is_default' => $role['ur_is_default']
					));
				}
			}
			
			foreach($privileges as $privilege) {
				$rs_query->insert(array($tables[2], $px[2]), array(
					'name' => $privilege['up_name'],
					'is_default' => $privilege['up_is_default']
				));
			}
			
			foreach($relationships as $relationship) {
				$rs_query->insert(array($tables[1], $px[1]), array(
					'role' => $relationship['ue_role'],
					'privilege' => $relationship['ue_privilege']
				));
			}
		}
	}
}

// Add `is_default` column to `user_privileges` table
function updateDB__1_3_14_beta() {
	global $rs_query;
	
	if(version_compare(RS_VERSION, '1.3.14-beta', '>=')) {
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
						'is_default' => 1
					), array(
						'id' => array('<=', 49)
					));
				}
			}
		}
	}
}

// Add `active_modules` setting and rename `theme` to `active_theme`
function updateDB__1_3_15_beta() {
	global $rs_query;
	
	if(version_compare(RS_VERSION, '1.3.15-beta', '>=')) {
		$active_modules = $rs_query->selectField(getTable('s'), 'id', array(
			'name' => 'active_modules'
		));
		
		if(empty($active_modules)) {
			$rs_query->insert(getTable('s'), array(
				'name' => 'active_modules',
				'value' => ''
			));
		}
		
		$active_theme = $rs_query->selectField(getTable('s'), 'value', array(
			'name' => 'theme'
		));
		
		if(!empty($active_theme)) {
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

// Prefixing database tables (this must run before the other scripts to avoid errors)
function updateDB__1_4_0_beta_snap_02() {
	global $rs_query;
	
	if(version_compare(RS_VERSION, '1.4.0-beta_snap-02', '>=')) {
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
	}
}