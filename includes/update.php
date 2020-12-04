<?php
/**
 * Update the CMS.
 * @since 1.1.0[b]{ss-01}
 */

// Check whether the version is higher than 1.0.9[b]
if(VERSION > '1.0.9') {
	// Fetch the database schema
	$schema = dbSchema();
	
	// Check whether the 'comments' table exists
	if(!$rs_query->tableExists('comments')) {
		// Create the table
		$rs_query->doQuery($schema['comments']);
	}
	
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