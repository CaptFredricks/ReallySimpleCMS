<?php
/**
 * Interface for admin pages.
 * @since 1.3.9-beta
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## METHODS [5] ##
 * { LISTS, FORMS, & ACTIONS [4] }
 * - public listRecords(): void
 * - public createRecord(): void
 * - public editRecord(): void
 * - public deleteRecord(): void
 * { MISCELLANEOUS [1] }
 * - public pageHeading(): void
 */
namespace Admin;

interface AdminInterface {
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all records in the database.
	 * @since 1.3.10-beta
	 */
	public function listRecords(): void;
	
	/**
	 * Create a new record.
	 * @since 1.3.10-beta
	 */
	public function createRecord(): void;
	
	/**
	 * Edit an existing record.
	 * @since 1.3.10-beta
	 */
	public function editRecord(): void;
	
	/**
	 * Delete an existing record.
	 * @since 1.3.10-beta
	 */
	public function deleteRecord(): void;
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.3.14-beta
	 */
	public function pageHeading(): void;
}