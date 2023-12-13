<?php
/**
 * This file handles structural changes to the database that would otherwise break the system.
 * The goal is to ensure that no existing data is lost during the update.
 * Old scripts will be removed after at least two major releases.
 * @since 1.3.5[b]
 */

// Adding logins
if(version_compare(CMS_VERSION, '1.2.0', '>=')) {
	$schema = dbSchema();
	
	// Try to create the `login_attempts` table
	if(!$rs_query->tableExists('login_attempts'))
		$rs_query->doQuery($schema['login_attempts']);
	
	// Try to create the `login_blacklist` table
	if(!$rs_query->tableExists('login_blacklist'))
		$rs_query->doQuery($schema['login_blacklist']);
	
	// Try to create the `login_rules` table
	if(!$rs_query->tableExists('login_rules'))
		$rs_query->doQuery($schema['login_rules']);
	
	// Check whether the proper user privileges exist for logins
	if($rs_query->select('user_privileges', 'COUNT(*)', array(
		'name' => array('LIKE', '%_login_%')
	)) !== 9) {
		
		// Check whether any non-default user roles exist
		if($rs_query->select('user_roles', 'COUNT(*)', array('id' => array('NOT IN', 1, 2, 3, 4))) > 0) {
			// Create a temporary `user_relationships` table
			$rs_query->doQuery("CREATE TABLE user_relationships_temp (
				id bigint(20) unsigned PRIMARY KEY auto_increment,
				role bigint(20) unsigned NOT NULL default '0',
				privilege bigint(20) unsigned NOT NULL default '0',
				KEY role (role),
				KEY privilege (privilege)
			);");
			
			// Fetch the custom user roles' ids
			$roles = $rs_query->select('user_roles', 'id', array('id' => array('NOT IN', 1, 2, 3, 4)));
			
			foreach($roles as $role) {
				$relationships = $rs_query->select('user_relationships', '*', array(
					'role' => $role['id']
				));
				
				foreach($relationships as $relationship) {
					$rs_query->insert('user_relationships_temp', array(
						'role' => $relationship['role'],
						'privilege' => $relationship['privilege']
					));
				}
			}
		}
		
		$rs_query->dropTables(array('user_privileges', 'user_relationships'));
		$rs_query->doQuery($schema['user_privileges']);
		$rs_query->doQuery($schema['user_relationships']);
		
		populateUserPrivileges();
		
		if($rs_query->tableExists('user_relationships_temp')) {
			foreach($roles as $role) {
				$relationships = $rs_query->select('user_relationships_temp', '*', array(
					'role' => $role['id']
				));
				
				foreach($relationships as $relationship) {
					// Update the privilege ids for privileges that have been reordered
					switch($relationship['privilege']) {
						case 36:
							$relationship['privilege'] = 45;
							break;
						case 37:
							$relationship['privilege'] = 46;
							break;
						case 38:
							$relationship['privilege'] = 47;
							break;
						case 39:
							$relationship['privilege'] = 48;
							break;
						case 40:
							$relationship['privilege'] = 49;
							break;
					}
					
					$rs_query->insert('user_relationships', array(
						'role' => $relationship['role'],
						'privilege' => $relationship['privilege']
					));
				}
			}
			
			$rs_query->dropTable('user_relationships_temp');
		}
	}
	
	// Check whether the 'track_login_attempts' setting exists
	if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name' => 'track_login_attempts')) > 0)
		$rs_query->insert('settings', array('name' => 'track_login_attempts', 'value' => 0));
	
	// Check whether the 'delete_old_login_attempts' setting exists
	if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name' => 'delete_old_login_attempts')) > 0)
		$rs_query->insert('settings', array('name' => 'delete_old_login_attempts', 'value' => 0));
	
	// Select all indexes for the `comments` table
	$indexes = $rs_query->showIndexes('comments');
	
	// Check whether the number of indexes is 4 (the primary key plus the other 3 indexes)
	if(count($indexes) !== 4) {
		// Create a temporary `comments` table
		$rs_query->doQuery("CREATE TABLE comments_temp (
			id bigint(20) unsigned PRIMARY KEY auto_increment,
			post bigint(20) unsigned NOT NULL default '0',
			author bigint(20) unsigned NOT NULL default '0',
			date datetime default NULL,
			content longtext NOT NULL default '',
			upvotes bigint(20) NOT NULL default '0',
			downvotes bigint(20) NOT NULL default '0',
			status varchar(20) NOT NULL default 'pending',
			parent bigint(20) unsigned NOT NULL default '0',
			KEY post (post),
			KEY author (author),
			KEY parent (parent)
		);");
		
		$comments = $rs_query->select('comments');
		
		foreach($comments as $comment) {
			$rs_query->insert('comments_temp', array(
				'post' => $comment['post'],
				'author' => $comment['author'],
				'date' => $comment['date'],
				'content' => $comment['content'],
				'upvotes' => $comment['upvotes'],
				'downvotes' => $comment['downvotes'],
				'status' => $comment['status'],
				'parent' => $comment['parent']
			));
		}
		
		$rs_query->dropTable('comments');
		$rs_query->doQuery("ALTER TABLE `comments_temp` RENAME TO `comments`;");
	}
}

// Tweaking post dates
if(version_compare(CMS_VERSION, '1.2.9', '>=')) {
	$posts = $rs_query->select('posts', array('id', 'date', 'modified', 'status'));
	
	foreach($posts as $post) {
		if(is_null($post['modified'])) {
			if(is_null($post['date'])) {
				$rs_query->update('posts',
					array('modified' => 'NOW()'),
					array('id' => $post['id'])
				);
			} else {
				$rs_query->update('posts',
					array('modified' => $post['date']),
					array('id' => $post['id'])
				);
			}
		}
		
		if(in_array($post['status'], array('draft', 'trash'), true))
			$rs_query->update('posts', array('date' => null), array('id' => $post['id']));
	}
}

// Tweaking media metadata
if(version_compare(CMS_VERSION, '1.3.5', '>=')) {
	if($rs_query->select('postmeta', 'COUNT(*)', array('datakey' => 'filename')) > 0) {
		$mediaa = $rs_query->select('posts', array('id', 'date'), array('type' => 'media'));
		
		foreach($mediaa as $media) {
			$year = formatDate($media['date'], 'Y');
			
			$meta = $rs_query->selectRow('postmeta', array('id', 'value'), array(
				'post' => $media['id'],
				'datakey' => 'filename'
			));
			
			$rs_query->update('postmeta', array(
				'datakey' => 'filepath',
				'value' => slash($year) . $meta['value']
			), array('id' => $meta['id']));
			
			if(!file_exists(slash(PATH . UPLOADS) . $year))
				mkdir(slash(PATH . UPLOADS) . $year);
			
			// Move the file
			$from = slash(PATH . UPLOADS) . $meta['value'];
			$to = slash(PATH . UPLOADS) . slash($year) . $meta['value'];
			if(!rename($from, $to)) exit('Unable to migrate uploaded files!');
		}
		
		$posts = $rs_query->select('posts', array('id', 'content'), array('type' => array(
			'NOT IN',
			'nav_menu_item',
			'media'
		)));
		
		foreach($posts as $post) {
			if(empty($post['content'])) continue;
			
			// Update media links in posts
			if(str_contains($post['content'], '/content/uploads') && 
				!preg_match('/\/content\/uploads\/(19|20)\d{2}/', $post['content'])) {
					
				$content = preg_replace('/\/content\/uploads/', '$0/' . $year, $post['content']);
				$rs_query->update('posts', array('content' => $content), array('id' => $post['id']));
			}
		}
	}
}

// Adding `display_name` and `dismissed_notices` usermeta to existing users
if(version_compare(CMS_VERSION, '1.3.8', '>=')) {
	$users = $rs_query->select('users', array('id', 'username'));
	
	foreach($users as $user) {
		$dname = $rs_query->selectRow('usermeta', 'COUNT(*)', array(
			'user' => $user['id'],
			'datakey' => 'display_name'
		));
		
		if($dname === 0) {
			$rs_query->insert('usermeta', array(
				'user' => $user['id'],
				'datakey' => 'display_name',
				'value' => $user['username']
			));
		}
		
		$dismissed = $rs_query->selectRow('usermeta', 'COUNT(*)', array(
			'user' => $user['id'],
			'datakey' => 'dismissed_notices'
		));
		
		if($dismissed === 0) {
			$rs_query->insert('usermeta', array(
				'user' => $user['id'],
				'datakey' => 'dismissed_notices',
				'value' => ''
			));
		}
	}
}

// Adding `index_post` metadata to existing posts
if(version_compare(CMS_VERSION, '1.3.9', '>=')) {
	$posts = $rs_query->select('posts', 'id');
	
	foreach($posts as $post) {
		$index = $rs_query->selectRow('postmeta', 'COUNT(*)', array(
			'post' => $post['id'],
			'datakey' => 'index_post'
		));
		
		if($index === 0) {
			$rs_query->insert('postmeta', array(
				'post' => $post['id'],
				'datakey' => 'index_post',
				'value' => getSetting('do_robots')
			));
		}
	}
}

// Various tweaks
if(version_compare(CMS_VERSION, '1.3.12', '>=')) {
	// Changing `unapproved` comments to `pending`
	$comments = $rs_query->select('comments', 'COUNT(status)', array(
		'status' => 'unapproved'
	));
	
	if($comments > 0) {
		$rs_query->update('comments', array(
			'status' => 'pending'
		), array(
			'status' => 'unapproved'
		));
	}
	
	// Adding `login_slug` setting
	$settings = $rs_query->selectRow('settings', 'COUNT(name)', array(
		'name' => 'login_slug'
	));
	
	if($settings === 0) {
		$rs_query->insert('settings', array(
			'name' => 'login_slug',
			'value' => ''
		));
	}
	
	// Updating table schemas and columns
	
	// `postmeta` table
	if($rs_query->columnExists('postmeta', '_key')) {
		$table = 'postmeta';
		
		// Add new columns
		$rs_query->doQuery("ALTER TABLE `{$table}` ADD datakey varchar(255) NOT NULL;");
		$rs_query->doQuery("ALTER TABLE `{$table}` ADD value_temp longtext NOT NULL default '';");
		
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
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP INDEX _key;");
		$rs_query->doQuery("CREATE INDEX datakey ON `{$table}` (datakey);");
		
		// Replace the old columns
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP COLUMN _key;");
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP COLUMN value;");
		$rs_query->doQuery("ALTER TABLE `{$table}` CHANGE `value_temp` `value` longtext NOT NULL default '';");
	}
	
	// `usermeta` table
	if($rs_query->columnExists('usermeta', '_key')) {
		$table = 'usermeta';
		
		// Add new columns
		$rs_query->doQuery("ALTER TABLE `{$table}` ADD datakey varchar(255) NOT NULL;");
		$rs_query->doQuery("ALTER TABLE `{$table}` ADD value_temp longtext NOT NULL default '';");
		
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
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP INDEX _key;");
		$rs_query->doQuery("CREATE INDEX datakey ON `{$table}` (datakey);");
		
		// Replace the old columns
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP COLUMN _key;");
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP COLUMN value;");
		$rs_query->doQuery("ALTER TABLE `{$table}` CHANGE `value_temp` `value` longtext NOT NULL default '';");
	}
	
	// `user_roles` table
	if($rs_query->columnExists('user_roles', '_default')) {
		$table = 'user_roles';
		
		// Add new column
		$rs_query->doQuery("ALTER TABLE `{$table}` ADD is_default tinyint(1) unsigned NOT NULL default '0';");
		
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
		$rs_query->doQuery("ALTER TABLE `{$table}` DROP COLUMN _default;");
	}
}