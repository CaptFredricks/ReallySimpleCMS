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
		// Add 'modal-open' class to the body tag
		$('body').addClass('modal-open');
		
		// Show the modal
		$('#modal-upload').fadeIn(100);
		
		// Add 'in' class to the modal
		$('#modal-upload').addClass('in');
	});
	
	/**
	 * Event handler for closing the modal window.
	 * @since 2.1.1[a]
	 */
	$('#modal-close').on('click', function() {
		// Close the modal
		modalClose();
	});
	
	/**
	 * Switch the modal tabs.
	 * @since 2.1.1[a]
	 */
	$('.modal-header .tabber .tab').on('click', function() {
		// Create a variable to hold the clicked element
		let self = this;
		
		// Check whether the clicked tab is active
		if(!$(self).hasClass('active')) {
			// Toggle the 'active' class
			$('.modal-header .tabber .tab').toggleClass('active');
			$('.modal-body .tab').toggleClass('active');
		}
		
		// Check whether the new active tab is the media tab
		if($('#media').hasClass('active')) {
			// Empty the media tab
			$('.media-wrap').empty();
			
			// Load the media library
			$('.media-wrap').load($(self).children().data('href') + '?media_type=' + $('#media-type').val());
		}
	});
	
	/**
	 * Select a media item.
	 * @since 2.1.2[a]
	 */
	$(document).on('click', '.media-item', function() {
		// Check whether the clicked item is already selected
		if(!$(this).hasClass('selected')) {
			// Remove the 'selected' class from all other items
			$('.media-item').removeClass('selected');
			
			// Add the 'selected' class
			$(this).addClass('selected');
			
			// Fetch the media item's hidden fields
			let fields = $(this).find('.hidden');
			
			// A variable to hold each field's name
			let field = '';
			
			// Loop through the fields
			$(fields).each(function() {
				// Fetch the field's name
				field = $(this).data('field');
				
				// Populate each field in the details section
				$('.media-details .' + field).html($(this).html());
			});
		} else {
			// Remove the 'selected' class
			$(this).removeClass('selected');
			
			// Clear the media details
			$('.media-details .field').empty();
		}
	});
	
	/**
	 * Select and insert media (via upload or media library).
	 * @since 2.1.3[a]
	 */
	$('#media-select').on('click', function() {
		if($('#upload').hasClass('active')) {
			// do something
		} else if($('#media').hasClass('active')) {
			// Fetch the hidden 'id' field and insert it on the form
			$('#media-id').val($('.media-item.selected .hidden[data-field="id"]').text());
			
			// Fetch the hidden 'filename' field and insert it on the form
			$('#media-thumbnail').attr('src', $('.media-item.selected .hidden[data-field="filename"] a').attr('href'));
		}
		
		// Close the modal
		modalClose();
	});
	
	/**
	 * Close the modal and perform cleanup.
	 * @since 2.1.3[a]
	 *
	 * @return null
	 */
	function modalClose() {
		// Remove 'modal-open' class from the body tag
		$('body').removeClass('modal-open');
		
		// Hide the modal
		$('#modal-upload').fadeOut(500);
		
		// Remove 'in' class from the modal
		$('#modal-upload').removeClass('in');
		
		// Remove the 'selected' class from any selected media items
		$('.media-item').removeClass('selected');
		
		// Clear the media details
		$('.media-details .field').empty();
	}
});