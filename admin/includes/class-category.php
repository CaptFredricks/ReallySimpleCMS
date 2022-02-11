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
	 * Alias for the Term class' listCategories function.
	 * @since 1.5.0[a]
	 *
	 * @see Term::listCategories()
	 * @access public
	 */
	public function listCategories(): void {
		$this->listTerms();
	}
	
	/**
	 * Create a category.
	 * Alias for the Term class' createCategory function.
	 * @since 1.5.0[a]
	 *
	 * @see Term::createCategory()
	 * @access public
	 */
	public function createCategory(): void {
		$this->createTerm();
	}
	
	/**
	 * Edit a category.
	 * Alias for the Term class' editCategory function.
	 * @since 1.5.1[a]
	 *
	 * @see Term::editCategory()
	 * @access public
	 */
	public function editCategory(): void {
		$this->editTerm();
	}
	
	/**
	 * Delete a category.
	 * Alias for the Term class' deleteCategory function.
	 * @since 1.5.1[a]
	 *
	 * @see Term::deleteCategory()
	 * @access public
	 */
	public function deleteCategory(): void {
		$this->deleteTerm();
	}
}