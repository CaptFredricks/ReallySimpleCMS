<?php
/**
 * Construct the database schema.
 * @since 1.2.6[a]
 *
 * @return array
 */
function dbSchema() {
	// Postmeta table
	$tables['postmeta'] = "CREATE TABLE postmeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		_key varchar(255) NOT NULL,
		value longtext NOT NULL,
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
		content longtext NOT NULL,
		status varchar(20) NOT NULL,
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
		value longtext NOT NULL,
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
		_default varchar(5) NOT NULL,
		KEY name (name)
	);";
	
	// Usermeta table
	$tables['usermeta'] = "CREATE TABLE usermeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		user bigint(20) unsigned NOT NULL default '0',
		_key varchar(255) NOT NULL,
		value longtext NOT NULL,
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
		KEY username (username),
		KEY role (role)
	);";
	
	return $tables;
}