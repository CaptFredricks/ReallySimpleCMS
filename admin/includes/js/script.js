/*!
 * Scripts for the admin dashboard.
 * @since 1.5.6[a]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	/*------------------------------*\
		BULK ACTIONS
	\*------------------------------*/
	
	/**
	 * Bulk select/deselect all records.
	 * @since 1.2.7[b]
	 */
	$('body').on('click', '.bulk-selector', function() {
		// Fetch the checked prop
		let is_checked = $(this).prop('checked');
		
		// Update the checkboxes
		$('.bulk-selector').prop('checked', is_checked);
		$('.bulk-select .checkbox').prop('checked', is_checked);
	});
	
	/**
	 * Handle checking/unchecking the other checkboxes.
	 * @since 1.2.7[b]
	 */
	$('body').on('click', '.bulk-select .checkbox', function() {
		// Create a flag to hold the checked state
		let is_checked;
		
		// Loop through the checkboxes
		$('.bulk-select .checkbox').each(function(idx, elem) {
			// Check whether the checkbox is checked
			if(!$(elem).prop('checked')) {
				// If not, set the flag to false and return
				is_checked = false;
				return false;
			}
			
			// Set the flag to true
			is_checked = true;
		});
		
		// Update the bulk selector checkboxes
		$('.bulk-selector').prop('checked', is_checked);
	});
	
	/**
	 * Bulk update all selected records.
	 * @since 1.2.7[b]
	 */
	$('body').on('click', '.bulk-update', function() {
		// Fetch the current page
		let page = $('body').attr('class');
		// Fetch the current action
		let action = $('.bulk-actions .actions').val();
		// Create an array to hold the selected checkbox values
		let selected = [];
		// Create a counter
		let i = 0;
		
		// Loop through the bulk select checkboxes
		$('.bulk-select .checkbox').each(function() {
			// Check whether the box has been checked
			if($(this).prop('checked')) {
				// Add the value to the selected array
				selected[i] = $(this).val();
				i++;
			}
		});
		
		// Check whether any boxes have been checked
		if(selected.length > 0) {
			// Clear the content area
			$('.content').empty();
			
			// Display a loading message
			$('.content').html('<div class="loading">Loading...</div>');
			
			$.ajax({
				data: {
					page: page,
					uri: window.location.pathname,
					action: action,
					selected: selected
				},
				dataType: 'html',
				method: 'POST',
				success: result => {
					// Suppress XMLHttpRequest warning
					$.ajaxPrefilter(function(options, originalOptions, jqXHR) { options.async = true; });
					
					// Replace the content
					$('.content').html(result);
				},
				url: '/admin/includes/bulk-actions.php'
			});
		}
	});
	
	/*------------------------------*\
		FORM VALIDATION
	\*------------------------------*/
	
	/**
	 * Remove the 'invalid' class from all fields that already have data.
	 * @since 2.1.9[a]
	 */
	(function() {
		// Loop through all required inputs that have not been changed
		$('.required.init').each(function() {
			// Check whether they have a value and remove the 'invalid' class if so
			if($(this).val().length > 0) $(this).removeClass('invalid');
		});
	})();
	
	/**
	 * Validate a required field.
	 * @since 2.1.9[a]
	 */
	$('.required').on('input', function() {
		// Check whether the field is a checkbox label
		if(!$(this).hasClass('checkbox-label')) {
			// Remove the 'init' class if it's present
			$(this).removeClass('init');

			// Check whether the field has any data in it
			if($(this).val().length > 0) {
				// Make the field valid
				$(this).removeClass('invalid').addClass('valid');

				// Check whether the field has the 'password-field' id
				if($(this).attr('id') === 'password-field') {
					// Remove the 'hidden' class from the checkbox label
					$('.checkbox-label').removeClass('hidden');
				}
			} else {
				// Make the field invalid
				$(this).removeClass('valid').addClass('invalid');

				// Check whether the field has the 'password-field' id
				if($(this).attr('id') === 'password-field') {
					// Add the 'hidden' class to the checkbox label
					$('.checkbox-label').addClass('hidden');
				}
			}
		}
	});
	
	/**
	 * Validate a required checkbox field.
	 * @since 2.1.9[a]
	 */
	$('.checkbox-input').on('click', function() {
		// Check whether the checkbox is a required field
		if($(this).parent().hasClass('required')) {
			// Remove the 'init' class from the checkbox field's label
			$(this).parent().removeClass('init');

			// Check whether the checkbox has been checked
			if($(this).prop('checked')) {
				// Make the field valid
				$(this).parent().removeClass('invalid').addClass('valid');
			} else {
				// Make the field invalid
				$(this).parent().removeClass('valid').addClass('invalid');
			}
		}
	});

	/**
	 * Remove the 'init' class from a required field when its focus is blurred.
	 * @since 2.1.9[a]
	 */
	$('.required').on('blur', function() {
		$(this).removeClass('init');
	});
	
	/**
	 * Try to submit the form.
	 * @since 2.1.9[a]
	 */
	$('.submit-input').on('click', function(e) {
		// Fetch the form whose submit button was just clicked
		let form = $(this).closest($('.data-form'));

		// Check whether any required fields are invalid
		if($(form).find('.invalid').length > 0) {
			// Prevent the default (submit) action
			e.preventDefault();

			// Remove the 'init' class from all required fields
			$(form).find('.required').removeClass('init');
		}
	});
	
	/**
	 * Show hidden conditional fields if appropriate.
	 * @since 1.2.0[b]{ss-04}
	 */
	$('.checkbox-label.conditional-toggle').each(function() {
		// Check whether the checkbox is checked and hide its conditional siblings if not
		if(!$(this).children('input').prop('checked')) $(this).siblings('.conditional-field').addClass('hidden');
	});
	
	$('.checkbox-label.conditional-toggle input').on('change', function() {
		// Fetch the conditional checkbox label
		let self = $(this).parent();
		
		// Check whether the checkbox is checked
		if($(self).children('input').prop('checked')) {
			// Display any hidden sibling fields
			$(self).siblings('.conditional-field').removeClass('hidden');
		} else {
			// Hide any sibling fields
			$(self).siblings('.conditional-field').addClass('hidden');
		}
	});
	
	/*------------------------------*\
		IMAGES
	\*------------------------------*/
	
	/**
	 * Remove an image.
	 * @since 2.1.5[a]
	 */
	$('.image-remove').on('click', function() {
		// Set the media's id field to zero
		$(this).parent().siblings('input[data-field="id"]').val(0);
		
		// Grey out the media thumbnail
		$(this).siblings('img[data-field="thumb"]').addClass('greyout');
	});
	
	/*------------------------------*\
		MISCELLANEOUS
	\*------------------------------*/
	
	/**
	 * Display the bars for the statistics graph.
	 * @since 1.5.6[a]
	 */
	(function(is_dash) {
		// Check whether the current page is the dashboard
		if(is_dash) {
			// Fetch the max count
			let max = $('#max-ct').val();
			
			// Loop through the bars
			$('.bar').each(function() {
				// Fetch the entry count
				let count = $(this).text();
				
				// Set the bar's height
				$(this).css({height: (count / max * 100) + '%'});
			});
		}
	})($('body').hasClass('dashboard'));
	
	/**
	 * Display information about an admin page.
	 * @since 1.2.0[b]
	 */
	$('.admin-info i').on('click', function() {
		let wrap = $(this).parent();
		
		if($(wrap).hasClass('open'))
			$(wrap).removeClass('open');
		else
			$(wrap).addClass('open');
	});
	
	/**
	 * Event handler to format a post slug.
	 * @since 2.1.9[a]
	 */
	$('#title-field').on('input', function() {
		formatSlug(this);
	});

	/**
	 * Event handler to format a term slug.
	 * @since 2.1.9[a]
	 */
	$('#name-field').on('input', function() {
 		formatSlug(this);
 	});
	
	/**
	 * Format a slug while editing the slug field.
	 * @since 2.1.9[a]
	 */
	$('#slug-field').on('input', function() {
		// Fetch the field's value
		let str = $(this).val();

		// Remove special characters
		str = str.replace(/[^\w\s-]/gi, '');

		// Replace spaces with hyphens
		str = str.replace(/[\s-]+/gi, '-');

		// Convert the slug to lowercase and display it
		$(this).val(str.toLowerCase());
	});
	
	/**
	 * Format a slug.
	 * @since 2.1.9[a]
	 *
	 * @param string field
	 * @return null
	 */
	function formatSlug(field) {
		// Fetch the field's value
		let str = $(field).val();

		// Trim off whitespace
		str = str.trim();

		// Remove special characters
		str = str.replace(/[^\w\s-]/gi, '');

		// Replace spaces with hyphens
		str = str.replace(/[\s-]+/gi, '-');

		// Convert the slug to lowercase and display it
		$('#slug-field').val(str.toLowerCase());

		// Remove the 'init' class from the slug field
		$('#slug-field').removeClass('init');

		// Check whether the slug field has any data in it
		if($('#slug-field').val().length === 0) {
			// Make the field invalid
			$('#slug-field').removeClass('valid').addClass('invalid');
		} else {
			// Make the field valid
			$('#slug-field').removeClass('invalid').addClass('valid');
		}
	}
	
	/**
	 * Select all/none on a checkbox list.
	 * @since 1.2.0[b]
	 */
	$('#select-all').on('click', function() {
		// Fetch the checkbox list
		let list = $(this).parents('.checkbox-list');
		
		// Check whether the checkbox is already checked
		if($(this).prop('checked')) {
			// Check all of the checkboxes in the list
			$(list).find('.checkbox-input').prop('checked', true);
			
			// Set the checkbox's label to 'SELECT NONE'
			$(this).siblings('span').text('SELECT NONE');
		} else {
			// Uncheck all of the checkboxes in the list
			$(list).find('.checkbox-input').prop('checked', false);
			
			// Set the checkbox's label to 'SELECT ALL'
			$(this).siblings('span').text('SELECT ALL');
		}
	});
	
	(function() {
		// Fetch the checkbox list
		let list = $('.checkbox-list');
		
		// Set a flag for whether all boxes are checked
		let all_checked = false;
		
		// Loop through the checkboxes
		$(list).find('.checkbox-input').each(function(i) {
			// Check whether the index is zero
			if(i !== 0) {
				// Check whether the box is checked
				if($(this).prop('checked')) {
					// Set the flag to true and break out of the loop
					all_checked = true;
					return false;
				}
			}
		});
		
		// Check whether all of the boxes are checked
		if(all_checked) {
			// Check the 'select all' checkbox
			$('#select-all').prop('checked', true);
			
			// Set the 'select all' checkbox's label to 'SELECT NONE'
			$('#select-all').siblings('span').text('SELECT NONE');
		}
	})();
	
	/**
	 * Event handler to generate a random password.
	 * @since 2.1.9[a]
	 */
	$('#password-gen').on('click', function() {
		// Generate the password and display it in the password field
		$('#password-field').val(generatePassword());

		// Remove the 'invalid' class from the password field
		$('#password-field').removeClass('invalid init').addClass('valid');

		// Remove the 'hidden' class from the checkbox label
		$('.checkbox-label').removeClass('hidden');
	});

	/**
 	 * Generate a random password.
 	 * @since 2.1.9[a]
	 *
	 * @param int length (optional; default: 16)
	 * @param bool special_chars (optional; default: true)
	 * @param bool extra_special_chars (optional; default: false)
	 * @return string
	 */
	function generatePassword(length = 16, special_chars = true, extra_special_chars = false) {
		// Regular characters
		let chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		// If desired, add the special characters
		if(special_chars) chars += '!@#$%^&*()';

		// If desired, add the extra special characters
		if(extra_special_chars) chars += '-_[]{}<>~`+=,.;:/?|';

		// Create an empty variable to hold the password
		let password = '';

		// Generate a random password
		for(let i = 0; i < parseInt(length); i++) {
			// Generate a random number
			let rand = Math.floor(Math.random() * chars.length);

			// Add a character to the password string
			password += chars.substring(rand, rand + 1);
		}

		// Return the password
		return password;
	}
});