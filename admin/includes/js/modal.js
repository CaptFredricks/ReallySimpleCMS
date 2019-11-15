/*!
 * Scripts for admin modal windows.
 * @since 2.1.1[a]
 */
jQuery(document).ready(function($) {
	// Turn on strict mode
	'use strict';
	
	/**
	 * Launch the upload/media library modal.
	 * @since 2.1.1[a]
	 */
	$('#modal-launch').on('click', function() {
		$('#modal-upload').fadeIn(250);
	});
	
	/**
	 * Close the upload/media library modal.
	 * @since 2.1.1[a]
	 */
	$('#modal-close').on('click', function() {
		$('#modal-upload').fadeOut(250);
	});
	
	/**
	 *
	 */
	$('.modal .tabber li').on('click', function() {
		// Check whether the clicked tab is active
		if(!$(this).hasClass('active')) {
			// Toggle the active class
			$('.modal .tabber li').toggleClass('active');
		}
	});
});