<?php
/**
 * Construct the database schema.
 * @since 1.2.6[a]
 *
 * @return array
 */
function dbSchema() {
	// Comments table
	$tables['comments'] = "CREATE TABLE comments (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		author bigint(20) unsigned NOT NULL default '0',
		date datetime default NULL,
		content longtext NOT NULL default '',
		upvotes bigint(20) NOT NULL default '0',
		downvotes bigint(20) NOT NULL default '0',
		status varchar(20) NOT NULL default 'unapproved',
		parent bigint(20) unsigned NOT NULL default '0'
	);";
	
	// Login_attempts table
	$tables['login_attempts'] = "CREATE TABLE login_attempts (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		login varchar(100) NOT NULL,
		ip_address varchar(150) NOT NULL,
		date datetime default NULL,
		status varchar(20) NOT NULL default 'failure',
		last_blacklisted_login datetime default NULL,
		last_blacklisted_ip datetime default NULL,
		KEY login_ip (login, ip_address)
	);";
	
	// Login_blacklist table
	$tables['login_blacklist'] = "CREATE TABLE login_blacklist (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(150) NOT NULL,
		attempts int(20) unsigned NOT NULL default '0',
		blacklisted datetime default NULL,
		duration bigint(20) unsigned NOT NULL default '0',
		reason text NOT NULL default '',
		KEY name (name)
	);";
	
	// Login_rules table
	$tables['login_rules'] = "CREATE TABLE login_rules (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		type varchar(255) NOT NULL default 'login',
		attempts int(20) unsigned NOT NULL default '0',
		duration bigint(20) unsigned NOT NULL default '0',
		KEY type (type)
	);";
	
	// Postmeta table
	$tables['postmeta'] = "CREATE TABLE postmeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		_key varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY post (post),
		KEY _key (_key)
	);";
	
	// Posts table
	$tables['posts'] = "CREATE TABLE posts (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		title varchar(255) NOT NULL,
		author bigint(20) unsigned NOT NULL default '0',
		date datetime default NULL,
		modified datetime default NULL,
		content longtext NOT NULL default '',
		status varchar(20) NOT NULL default 'inherit',
		slug varchar(255) NOT NULL,
		parent bigint(20) unsigned NOT NULL default '0',
		type varchar(50) NOT NULL default 'post',
		KEY author (author),
		KEY slug (slug),
		KEY parent (parent)
	);";
	
	// Redirects table
	$tables['redirects'] = "CREATE TABLE redirects (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		slug varchar(255) NOT NULL,
		KEY post (post)
	);";
	
	// Settings table
	$tables['settings'] = "CREATE TABLE settings (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY name (name)
	);";
	
	// Taxonomies table
	$tables['taxonomies'] = "CREATE TABLE taxonomies (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	// Term_relationships table
	$tables['term_relationships'] = "CREATE TABLE term_relationships (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		term bigint(20) unsigned NOT NULL default '0',
		post bigint(20) unsigned NOT NULL default '0',
		KEY term (term),
		KEY post (post)
	);";
	
	// Terms table
	$tables['terms'] = "CREATE TABLE terms (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		slug varchar(255) NOT NULL,
		taxonomy bigint(20) unsigned NOT NULL default '0',
		parent bigint(20) unsigned NOT NULL default '0',
		count bigint(20) unsigned NOT NULL default '0',
		KEY slug (slug),
		KEY taxonomy (taxonomy)
	);";
	
	// User_privileges table
	$tables['user_privileges'] = "CREATE TABLE user_privileges (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	// User_relationships table
	$tables['user_relationships'] = "CREATE TABLE user_relationships (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		role bigint(20) unsigned NOT NULL default '0',
		privilege bigint(20) unsigned NOT NULL default '0',
		KEY role (role),
		KEY privilege (privilege)
	);";
	
	// User_roles table
	$tables['user_roles'] = "CREATE TABLE user_roles (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		_default varchar(5) NOT NULL default 'no',
		KEY name (name)
	);";
	
	// Usermeta table
	$tables['usermeta'] = "CREATE TABLE usermeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		user bigint(20) unsigned NOT NULL default '0',
		_key varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY user (user),
		KEY _key (_key)
	);";
	
	// Users table
	$tables['users'] = "CREATE TABLE users (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		username varchar(100) NOT NULL,
		password varchar(255) NOT NULL,
		email varchar(100) NOT NULL,
		registered datetime default NULL,
		last_login datetime default NULL,
		session varchar(255) default NULL,
		role bigint(20) unsigned NOT NULL default '0',
		security_key varchar(255) default NULL,
		KEY username (username),
		KEY role (role)
	);";
	
	// Return the table schemas
	return $tables;
}