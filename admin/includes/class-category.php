<?php
/**
 * Admin class used to implement the Category object. Inherits from the Term class.
 * @since 1.5.0[a]
 *
 * Categories are used to group posts by similarity. Categories are only used on the default post type: 'post'.
 * Categories can be created, modified, and deleted. They are stored in the 'terms' database table.
 */
class Category extends Term {
	/**
	 * Construct a list of all categories in the database.
	 * @since 1.5.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function listCategories() {
		$this->listTerms();
	}
	
	/**
	 * Construct the 'Create Category' form.
	 * @since 1.5.0[a]
	 *
	 * @access public
	 * @return null
	 */
	public function createCategory() {
		$this->createTerm();
	}
	
	/**
	 * Construct the 'Edit Category' form.
	 * @since 1.5.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function editCategory() {
		$this->editTerm();
	}
	
	/**
	 * Delete a category from the database.
	 * @since 1.5.1[a]
	 *
	 * @access public
	 * @return null
	 */
	public function deleteCategory() {
		$this->deleteTerm();
	}
}