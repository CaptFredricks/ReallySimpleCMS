<?php
/**
 * The database schema. This outlines all of the database tables and how they should be constructed.
 * @since 1.2.6-alpha
 *
 * @package ReallySimpleCMS
 */

/**
 * Construct the schema.
 * @since 1.2.6-alpha
 *
 * @return array
 */
function dbSchema(): array {
	/**
	 * `comments` table -- Stores post comments.
	 * @since 1.1.0[b]_snap-01
	 */
	$tables['comments'] = "CREATE TABLE comments (
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
	);";
	
	/**
	 * `login_attempts` table -- Stores all login attempts.
	 * @since 1.2.0[b]_snap-01
	 */
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
	
	/**
	 * `login_blacklist` table -- Stores all blacklisted logins.
	 * @since 1.2.0[b]_snap-01
	 */
	$tables['login_blacklist'] = "CREATE TABLE login_blacklist (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(150) NOT NULL,
		attempts int(20) unsigned NOT NULL default '0',
		blacklisted datetime default NULL,
		duration bigint(20) unsigned NOT NULL default '0',
		reason text NOT NULL default '',
		KEY name (name)
	);";
	
	/**
	 * `login_rules` table -- Stores all login rules.
	 * @since 1.2.0[b]_snap-01
	 */
	$tables['login_rules'] = "CREATE TABLE login_rules (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		type varchar(255) NOT NULL default 'login',
		attempts int(20) unsigned NOT NULL default '0',
		duration bigint(20) unsigned NOT NULL default '0',
		KEY type (type)
	);";
	
	/**
	 * `postmeta` table -- Stores metadata for posts.
	 * @since 1.3.5[a]
	 */
	$tables['postmeta'] = "CREATE TABLE postmeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		datakey varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY post (post),
		KEY datakey (datakey)
	);";
	
	/**
	 * `posts` table -- Stores posts of all types, including custom ones.
	 * @since 1.3.5[a]
	 */
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
	
	/**
	 * `redirects` table -- Stores post redirects (unused).
	 * @since 1.3.5[a]
	 */
	$tables['redirects'] = "CREATE TABLE redirects (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		post bigint(20) unsigned NOT NULL default '0',
		slug varchar(255) NOT NULL,
		KEY post (post)
	);";
	
	/**
	 * `settings` table -- Stores site-wide settings.
	 * @since 1.3.5[a]
	 */
	$tables['settings'] = "CREATE TABLE settings (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY name (name)
	);";
	
	/**
	 * `taxonomies` table -- Stores taxonomy data.
	 * @since 1.4.10[a]
	 */
	$tables['taxonomies'] = "CREATE TABLE taxonomies (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	/**
	 * `term_relationships` table -- Stores relationships between `terms` and `posts`.
	 * @since 1.4.10[a]
	 */
	$tables['term_relationships'] = "CREATE TABLE term_relationships (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		term bigint(20) unsigned NOT NULL default '0',
		post bigint(20) unsigned NOT NULL default '0',
		KEY term (term),
		KEY post (post)
	);";
	
	/**
	 * `terms` table -- Stores terms of all types, including custom ones.
	 * @since 1.4.10[a]
	 */
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
	
	/**
	 * `user_privileges` table -- Stores user privilege data.
	 * @since 1.3.5[a]
	 */
	$tables['user_privileges'] = "CREATE TABLE user_privileges (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		KEY name (name)
	);";
	
	/**
	 * `user_relationships` table -- Stores relationships between `user_roles` and `user_privileges`.
	 * @since 1.3.5[a]
	 */
	$tables['user_relationships'] = "CREATE TABLE user_relationships (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		role bigint(20) unsigned NOT NULL default '0',
		privilege bigint(20) unsigned NOT NULL default '0',
		KEY role (role),
		KEY privilege (privilege)
	);";
	
	/**
	 * `user_roles` table -- Stores user role data.
	 * @since 1.3.5[a]
	 */
	$tables['user_roles'] = "CREATE TABLE user_roles (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		name varchar(255) NOT NULL,
		is_default tinyint(1) unsigned NOT NULL default '0',
		KEY name (name)
	);";
	
	/**
	 * `usermeta` table -- Stores metadata for users.
	 * @since 1.3.5[a]
	 */
	$tables['usermeta'] = "CREATE TABLE usermeta (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		user bigint(20) unsigned NOT NULL default '0',
		datakey varchar(255) NOT NULL,
		value longtext NOT NULL default '',
		KEY user (user),
		KEY datakey (datakey)
	);";
	
	/**
	 * `users` table -- Stores user data.
	 * @since 1.3.5[a]
	 */
	$tables['users'] = "CREATE TABLE users (
		id bigint(20) unsigned PRIMARY KEY auto_increment,
		username varchar(100) NOT NULL,
		password varchar(255) NOT NULL,
		email varchar(100) NOT NULL,
		registered datetime default NULL,
		last_login datetime default NULL,
		session varchar(50) default NULL,
		role bigint(20) unsigned NOT NULL default '0',
		security_key varchar(50) default NULL,
		KEY username (username),
		KEY role (role)
	);";
	
	return $tables;
}