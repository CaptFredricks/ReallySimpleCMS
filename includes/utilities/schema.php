<?php
/**
 * The database schema. This outlines all of the database tables and how they should be constructed.
 * @since 1.2.6-alpha
 *
 * @package ReallySimpleCMS
 *
 * TABLE LIST:
 *
 * ## POSTS ##
 * { `posts`, `postmeta`, `comments`, `redirects` }
 * ## TERMS ##
 * { `terms`, `taxonomies`, `term_relationships` }
 * ## USERS ##
 * { `users`, `usermeta`, `user_roles`, `user_privileges`, `user_relationships` }
 * ## LOGINS ##
 * { `login_attempts`, `login_blacklist`, `login_rules` }
 * ## MISCELLANEOUS ##
 * { `settings` }
 */

/**
 * Construct the schema.
 * @since 1.2.6-alpha
 *
 * @return array
 */
function dbSchema(): array {
	
	/*------------------------------------*\
		POSTS
	\*------------------------------------*/
	
	/**
	 * `posts` table -- Stores posts of all types, including custom ones.
	 * @since 1.3.5-alpha
	 */
	$tables['posts'] = array(
		"p_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"p_title varchar(255) NOT NULL,",
		"p_author bigint(20) unsigned NOT NULL default '0',",
		"p_created datetime default NULL,",
		"p_modified datetime default NULL,",
		"p_content longtext NOT NULL,",
		"p_status varchar(20) NOT NULL default 'inherit',",
		"p_slug varchar(255) NOT NULL default '',",
		"p_parent bigint(20) unsigned NOT NULL default '0',",
		"p_type varchar(50) NOT NULL default 'post',",
		"KEY author (p_author),",
		"KEY slug (p_slug),",
		"KEY parent (p_parent)"
	);
	
	/**
	 * `postmeta` table -- Stores metadata for posts.
	 * @since 1.3.5-alpha
	 */
	$tables['postmeta'] = array(
		"pm_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"pm_post bigint(20) unsigned NOT NULL default '0',",
		"pm_key varchar(255) NOT NULL,",
		"pm_value longtext NOT NULL,",
		"KEY post (pm_post),",
		"KEY metakey (pm_key)"
	);
	
	/**
	 * `comments` table -- Stores post comments.
	 * @since 1.1.0-beta_snap-01
	 */
	$tables['comments'] = array(
		"c_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"c_post bigint(20) unsigned NOT NULL default '0',",
		"c_author bigint(20) unsigned NOT NULL default '0',",
		"c_created datetime default NULL,",
		"c_content longtext NOT NULL,",
		"c_upvotes bigint(20) NOT NULL default '0',",
		"c_downvotes bigint(20) NOT NULL default '0',",
		"c_status varchar(20) NOT NULL default 'pending',",
		"c_parent bigint(20) unsigned NOT NULL default '0',",
		"KEY post (c_post),",
		"KEY author (c_author),",
		"KEY parent (c_parent)"
	);
	
	/**
	 * `redirects` table -- Stores post redirects (unused).
	 * @since 1.3.5-alpha
	 */
	$tables['redirects'] = array(
		"r_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"r_post bigint(20) unsigned NOT NULL default '0',",
		"r_slug varchar(255) NOT NULL default '',",
		"KEY post (r_post)"
	);
	
	/*------------------------------------*\
		TERMS
	\*------------------------------------*/
	
	/**
	 * `terms` table -- Stores terms of all types, including custom ones.
	 * @since 1.4.10-alpha
	 */
	$tables['terms'] = array(
		"t_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"t_name varchar(255) NOT NULL,",
		"t_slug varchar(255) NOT NULL default '',",
		"t_taxonomy bigint(20) unsigned NOT NULL default '0',",
		"t_parent bigint(20) unsigned NOT NULL default '0',",
		"t_count bigint(20) unsigned NOT NULL default '0',",
		"KEY slug (t_slug),",
		"KEY taxonomy (t_taxonomy)"
	);
	
	/**
	 * `taxonomies` table -- Stores taxonomy data.
	 * @since 1.4.10-alpha
	 */
	$tables['taxonomies'] = array(
		"ta_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"ta_name varchar(255) NOT NULL,",
		"KEY name (ta_name)"
	);
	
	/**
	 * `term_relationships` table -- Stores relationships between `terms` and `posts`.
	 * @since 1.4.10-alpha
	 */
	$tables['term_relationships'] = array(
		"tr_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"tr_term bigint(20) unsigned NOT NULL default '0',",
		"tr_post bigint(20) unsigned NOT NULL default '0',",
		"KEY term (tr_term),",
		"KEY post (tr_post)"
	);
	
	/*------------------------------------*\
		USERS
	\*------------------------------------*/
	
	/**
	 * `users` table -- Stores user data.
	 * @since 1.3.5-alpha
	 */
	$tables['users'] = array(
		"u_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"u_username varchar(100) NOT NULL,",
		"u_password varchar(255) NOT NULL,",
		"u_email varchar(100) NOT NULL,",
		"u_registered datetime default NULL,",
		"u_last_login datetime default NULL,",
		"u_session varchar(50) default NULL,",
		"u_role bigint(20) unsigned NOT NULL default '0',",
		"u_token varchar(50) default NULL,",
		"KEY username (u_username),",
		"KEY role (u_role)"
	);
	
	/**
	 * `usermeta` table -- Stores metadata for users.
	 * @since 1.3.5-alpha
	 */
	$tables['usermeta'] = array(
		"um_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"um_user bigint(20) unsigned NOT NULL default '0',",
		"um_key varchar(255) NOT NULL,",
		"um_value longtext NOT NULL,",
		"KEY user (um_user),",
		"KEY metakey (um_key)"
	);
	
	/**
	 * `user_roles` table -- Stores user role data.
	 * @since 1.3.5-alpha
	 */
	$tables['user_roles'] = array(
		"ur_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"ur_name varchar(255) NOT NULL,",
		"ur_level int(20) unsigned NOT NULL default '0',",
		"ur_is_default tinyint(1) unsigned NOT NULL default '0',",
		"KEY name (ur_name)"
	);
	
	/**
	 * `user_privileges` table -- Stores user privilege data.
	 * @since 1.3.5-alpha
	 */
	$tables['user_privileges'] = array(
		"up_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"up_name varchar(255) NOT NULL,",
		"up_is_default tinyint(1) unsigned NOT NULL default '0',",
		"KEY name (up_name)"
	);
	
	/**
	 * `user_relationships` table -- Stores relationships between `user_roles` and `user_privileges`.
	 * @since 1.3.5-alpha
	 */
	$tables['user_relationships'] = array(
		"ue_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"ue_role bigint(20) unsigned NOT NULL default '0',",
		"ue_privilege bigint(20) unsigned NOT NULL default '0',",
		"KEY role (ue_role),",
		"KEY privilege (ue_privilege)"
	);
	
	/*------------------------------------*\
		LOGINS
	\*------------------------------------*/
	
	/**
	 * `login_attempts` table -- Stores all login attempts.
	 * @since 1.2.0-beta_snap-01
	 */
	$tables['login_attempts'] = array(
		"la_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"la_login varchar(100) NOT NULL,",
		"la_ip_address varchar(150) NOT NULL,",
		"la_date datetime default NULL,",
		"la_status varchar(20) NOT NULL default 'failure',",
		"la_last_blacklisted_login datetime default NULL,",
		"la_last_blacklisted_ip datetime default NULL,",
		"KEY login_ip (la_login, la_ip_address)"
	);
	
	/**
	 * `login_blacklist` table -- Stores all blacklisted logins.
	 * @since 1.2.0-beta_snap-01
	 */
	$tables['login_blacklist'] = array(
		"lb_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"lb_name varchar(150) NOT NULL,",
		"lb_attempts int(20) unsigned NOT NULL default '0',",
		"lb_blacklisted datetime default NULL,",
		"lb_duration bigint(20) unsigned NOT NULL default '0',",
		"lb_reason text NOT NULL,",
		"KEY name (lb_name)"
	);
	
	/**
	 * `login_rules` table -- Stores all login rules.
	 * @since 1.2.0-beta_snap-01
	 */
	$tables['login_rules'] = array(
		"lr_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"lr_type varchar(255) NOT NULL default 'login',",
		"lr_attempts int(20) unsigned NOT NULL default '0',",
		"lr_duration bigint(20) unsigned NOT NULL default '0',",
		"KEY rule_type (lr_type)"
	);
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * `settings` table -- Stores site-wide settings.
	 * @since 1.3.5-alpha
	 */
	$tables['settings'] = array(
		"s_id bigint(20) unsigned PRIMARY KEY auto_increment,",
		"s_name varchar(255) NOT NULL,",
		"s_value longtext NOT NULL,",
		"KEY name (s_name)"
	);
	
	return $tables;
}