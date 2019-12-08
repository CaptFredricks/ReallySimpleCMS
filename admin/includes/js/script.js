/*!
 * Scripts for the admin dashboard.
 * @since 1.5.6[a]
 */
jQuery(document).ready(function($) {
	// Turn on strict mode
	'use strict';
	
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
	
	/*------------------------------*\
		FEATURED IMAGES
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