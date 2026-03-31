<?php
/**
 * Enum to hold all database table names.
 * @since 1.3.15-beta
 *
 * ## METHODS [3] ##
 * { GETTER METHODS [3] }
 * - public static getTable(string $key): string|array
 * - public static getTableName(string $key): string
 * - public static getTablePrefix(string $key): string
 */
namespace Enums;

enum Table: string {
	// Posts
	case posts = 'p';
	case postmeta = 'pm';
	case comments = 'c';
	case redirects = 'r';
	
	// Terms
	case terms = 't';
	case taxonomies = 'ta';
	case term_relationships = 'tr';
	
	// Users
	case users = 'u';
	case usermeta = 'um';
	case user_roles = 'ur';
	case user_privileges = 'up';
	case user_relationships = 'ue';
	
	// Logins
	case login_attempts = 'la';
	case login_blacklist = 'lb';
	case login_rules = 'lr';
	
	// Miscellaneous
	case settings = 's';
	
	/*------------------------------------*\
		GETTER METHODS
	\*------------------------------------*/
	
	/**
	 * Fetch a database table based on its key.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $key -- The table key.
	 * @return string|array
	 */
	public static function getTable(string $key): string|array {
		#return Table::getTableName($key);
		return array(Table::getTableName($key), Table::getTablePrefix($key));
	}
	
	/**
	 * Fetch a database table's name based on its key.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $key -- The table key.
	 * @return string
	 */
	public static function getTableName(string $key): string {
		$table = Table::from($key);
		
		return $table->name;
	}
	
	/**
	 * Fetch a database table's prefix based on its key.
	 * @since 1.3.15-beta
	 *
	 * @access public
	 * @param string $key -- The table key.
	 * @return string
	 */
	public static function getTablePrefix(string $key): string {
		$table = Table::from($key);
		
		return $table->value . '_';
	}
}