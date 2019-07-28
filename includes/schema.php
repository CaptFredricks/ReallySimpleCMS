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
		date datetime NOT NULL default '0000-00-00 00:00:00',
		modified datetime NOT NULL default '0000-00-00 00:00:00',
		content longtext NOT NULL,
		status varchar(20) NOT NULL,
		slug varchar(255) NOT NULL,
		parent bigint(20) unsigned NOT NULL default '0',
		type varchar(50) NOT NULL default 'post',
		KEY author (author),
		KEY slug (slug),
		KEY parent (parent)
	);";
	
	// Privileges table
	$tables['privileges'] = "CREATE TABLE privileges (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	// Redirects table
	$tables['redirects'] = "CREATE TABLE redirects (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		slug varchar(255) NOT NULL,
		KEY post (post)
	);";
	
	// Roles table
	$tables['roles'] = "CREATE TABLE roles (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	// Rp_relationships table
	$tables['rp_relationships'] = "CREATE TABLE rp_relationships (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		role bigint(20) unsigned NOT NULL default '0',
		privilege bigint(20) unsigned NOT NULL default '0',
		KEY role (role),
		KEY privilege (privilege)
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
		registered datetime NOT NULL default '0000-00-00 00:00:00',
		last_login datetime default NULL,
		session varchar(255) default NULL,
		role bigint(20) unsigned NOT NULL default '0',
		KEY username (username),
		KEY role (role)
	);";
	
	return $tables;
}