<?php
/**
 * Update the CMS.
 * @since 1.1.0[b]{ss-01}
 */

// Check whether the version is higher than 1.0.9[b]
if(VERSION > '1.0.9') {
	// Fetch the database schema
	$schema = dbSchema();
	
	// Check whether the 'comments' table exists and create it if not
	if(!$rs_query->tableExists('comments'))
		$rs_query->doQuery($schema['comments']);
	
	// Loop through the post types array
	foreach($post_types as $post_type) {
		// Check whether the post type has comments enabled
		if($post_type['comments']) {
			// Fetch all posts of the specified post type from the database
			$posts = $rs_query->select('posts', 'id', array('type'=>$post_type['name']));
			
			// Loop through the posts
			foreach($posts as $post) {
				// Check whether the post's 'comment_status' metadata entry exists
				if(!$rs_query->selectRow('postmeta', 'COUNT(*)', array('post'=>$post['id'], '_key'=>'comment_status')) > 0) {
					// Insert a new metadata entry into the database
					$rs_query->insert('postmeta', array('post'=>$post['id'], '_key'=>'comment_status', 'value'=>'1'));
				}
				
				// Check whether the post's 'comment_count' metadata entry exists
				if(!$rs_query->selectRow('postmeta', 'COUNT(*)', array('post'=>$post['id'], '_key'=>'comment_count')) > 0) {
					// Insert a new metadata entry into the database
					$rs_query->insert('postmeta', array('post'=>$post['id'], '_key'=>'comment_count', 'value'=>'0'));
				}
			}
		}
	}
	
	// Check whether the proper user privileges exist for comments
	if($rs_query->select('user_privileges', 'COUNT(*)', array('name'=>array('LIKE', '%_comments'))) !== 3) {
		// Delete the 'user_privileges' and 'user_relationships' tables
		$rs_query->doQuery("DROP TABLE `user_privileges`, `user_relationships`;");
		
		// Recreate the tables
		$rs_query->doQuery($schema['user_privileges']);
		$rs_query->doQuery($schema['user_relationships']);
		
		// Populate the tables
		populateUserPrivileges();
	}
}

// Check whether the version is higher than 1.1.6[b]
if(VERSION > '1.1.6') {
	// Check whether the 'comment_status' setting exists
	if($rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'comment_status')) > 0) {
		// Rename the setting to 'enable_comments'
		$rs_query->update('settings', array('name'=>'enable_comments'), array('name'=>'comment_status'));
	} else {
		// Check whether the 'enable_comments' setting exists
		if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'enable_comments')) > 0) {
			// Insert a new setting named 'enable_comments' into the database
			$rs_query->insert('settings', array('name'=>'enable_comments', 'value'=>1));
		}
	}
	
	// Check whether the 'comment_approval' setting exists
	if($rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'comment_approval')) > 0) {
		// Rename the setting to 'auto_approve_comments'
		$rs_query->update('settings', array('name'=>'auto_approve_comments'), array('name'=>'comment_approval'));
	} else {
		// Check whether the 'auto_approve_comments' setting exists
		if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'auto_approve_comments')) > 0) {
			// Insert a new setting named 'auto_approve_comments' into the database
			$rs_query->insert('settings', array('name'=>'auto_approve_comments', 'value'=>0));
		}
	}
	
	// Check whether the 'allow_anon_comments' setting exists
	if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'allow_anon_comments')) > 0) {
		// Insert the new setting into the database
		$rs_query->insert('settings', array('name'=>'allow_anon_comments', 'value'=>0));
	}
}

// Check whether the version is higher than 1.1.7[b]
if(VERSION > '1.1.7') {
	// Fetch the database schema
	$schema = dbSchema();
	
	// Check whether the 'login_attempts' table exists and create it if not
	if(!$rs_query->tableExists('login_attempts'))
		$rs_query->doQuery($schema['login_attempts']);
	
	// Check whether the 'login_blacklist' table exists and create it if not
	if(!$rs_query->tableExists('login_blacklist'))
		$rs_query->doQuery($schema['login_blacklist']);
	
	// Check whether the 'login_rules' table exists and create it if not
	if(!$rs_query->tableExists('login_rules'))
		$rs_query->doQuery($schema['login_rules']);
	
	// Check whether the proper user privileges exist for logins
	if($rs_query->select('user_privileges', 'COUNT(*)', array('name'=>array('LIKE', '%_login_%'))) !== 9) {
		// Check whether any non-default user roles exist
		if($rs_query->select('user_roles', 'COUNT(*)', array('id'=>array('NOT IN', 1, 2, 3, 4))) > 0) {
			// Fetch the custom user roles' ids
			$roles = $rs_query->select('user_roles', 'id', array('id'=>array('NOT IN', 1, 2, 3, 4)));
			
			// Create a temporary 'user_relationships' table
			$rs_query->doQuery("CREATE TABLE user_relationships_temp (
				id bigint(20) unsigned PRIMARY KEY auto_increment,
				role bigint(20) unsigned NOT NULL default '0',
				privilege bigint(20) unsigned NOT NULL default '0',
				KEY role (role),
				KEY privilege (privilege)
			);");
			
			// Loop through the custom user roles' ids
			foreach($roles as $role) {
				// Fetch all relationships related to the roles from the database
				$relationships = $rs_query->select('user_relationships', '*', array('role'=>$role['id']));
				
				// Loop through the relationships
				foreach($relationships as $relationship) {
					// Insert the relationships into the temporary table
					$rs_query->insert('user_relationships_temp', array('role'=>$relationship['role'], 'privilege'=>$relationship['privilege']));
				}
			}
		}
		
		// Delete the 'user_privileges' and 'user_relationships' tables
		$rs_query->doQuery("DROP TABLE `user_privileges`, `user_relationships`;");
		
		// Recreate the tables
		$rs_query->doQuery($schema['user_privileges']);
		$rs_query->doQuery($schema['user_relationships']);
		
		// Populate the tables
		populateUserPrivileges();
		
		// Check whether a temporary 'user_relationships' table exists
		if($rs_query->tableExists('user_relationships_temp')) {
			// Loop through the custom user roles' ids
			foreach($roles as $role) {
				// Fetch all relationships related to the roles from the database
				$relationships = $rs_query->select('user_relationships_temp', '*', array('role'=>$role['id']));
				
				// Loop through the relationships
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
					
					// Insert the relationships into the database
					$rs_query->insert('user_relationships', array('role'=>$relationship['role'], 'privilege'=>$relationship['privilege']));
				}
			}
			
			// Delete the temporary 'user_relationships' table
			$rs_query->doQuery("DROP TABLE `user_relationships_temp`;");
		}
	}
	
	// Check whether the 'track_login_attempts' setting exists
	if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'track_login_attempts')) > 0) {
		// Insert the new setting into the database
		$rs_query->insert('settings', array('name'=>'track_login_attempts', 'value'=>0));
	}
	
	// Check whether the 'delete_old_login_attempts' setting exists
	if(!$rs_query->selectRow('settings', 'COUNT(*)', array('name'=>'delete_old_login_attempts')) > 0) {
		// Insert the new setting into the database
		$rs_query->insert('settings', array('name'=>'delete_old_login_attempts', 'value'=>0));
	}
}