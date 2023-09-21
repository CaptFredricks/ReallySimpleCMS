<?php
/**
 * Interface for admin pages.
 * @since 1.3.9[b]
 */
interface AdminInterface {
	/**
	 * Construct a list of all records in the database.
	 * @since 1.3.10[b]
	 */
	public function listRecords(): void;
	
	/**
	 * Create a new record.
	 * @since 1.3.10[b]
	 */
	public function createRecord(): void;
	
	/**
	 * Edit an existing record.
	 * @since 1.3.10[b]
	 */
	public function editRecord(): void;
	
	/**
	 * Delete an existing record.
	 * @since 1.3.10[b]
	 */
	public function deleteRecord(): void;
}