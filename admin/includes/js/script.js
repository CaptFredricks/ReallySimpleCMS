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
		let is_checked = $(this).prop('checked');
		
		$('.bulk-selector').prop('checked', is_checked);
		$('.bulk-select .checkbox').prop('checked', is_checked);
	});
	
	/**
	 * Handle checking/unchecking the other checkboxes.
	 * @since 1.2.7[b]
	 */
	$('body').on('click', '.bulk-select .checkbox', function() {
		let is_checked;
		
		$('.bulk-select .checkbox').each(function(idx, elem) {
			if(!$(elem).prop('checked')) {
				is_checked = false;
				return false;
			}
			
			is_checked = true;
		});
		
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
		
		let selected = [];
		let i = 0;
		
		$('.bulk-select .checkbox').each(function() {
			if($(this).prop('checked')) {
				selected[i] = $(this).val();
				i++;
			}
		});
		
		// Check whether any boxes have been checked
		if(selected.length > 0) {
			$('.content').empty();
			$('.content').html('<div class="loading">Loading...</div>');
			
			$.ajax({
				data: {
					page: page,
					uri: window.location.pathname + window.location.search,
					action: action,
					selected: selected
				},
				dataType: 'html',
				error: (request, status, error) => {
					console.log(error);
				},
				method: 'POST',
				success: result => {
					// Suppress XMLHttpRequest warning
					$.ajaxPrefilter(function(options, originalOptions, jqXHR) { options.async = true; });
					
					// Redirect to the current page (preserving query params)
					window.location = window.location.href;
					
					// Replace the content
					$('.content').html(result);
				},
				url: '/admin/includes/bulk-actions.php'
			});
		}
	});
	
	/**
	 * Bulk delete all spam comments.
	 * @since 1.3.7[b]
	 */
	$('body').on('click', '.bulk-delete-spam', function() {
		$('.content').empty();
		$('.content').html('<div class="loading">Loading...</div>');
		
		$.ajax({
			data: {
				page: 'comments',
				uri: window.location.pathname + window.location.search,
				action: 'delete_spam'
			},
			dataType: 'html',
			error: (request, status, error) => {
				console.log(error);
			},
			method: 'POST',
			success: result => {
				// Suppress XMLHttpRequest warning
				$.ajaxPrefilter(function(options, originalOptions, jqXHR) { options.async = true; });
				
				// Redirect to the current page (preserving query params)
				window.location = window.location.href;
				
				// Replace the content
				$('.content').html(result);
			},
			url: '/admin/includes/bulk-actions.php'
		});
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
			// Validate any inputs with content
			if($(this).val().length > 0) $(this).removeClass('invalid');
		});
	})();
	
	/**
	 * Validate a required field.
	 * @since 2.1.9[a]
	 */
	$('.required').on('input', function() {
		if(!$(this).hasClass('checkbox-label')) {
			$(this).removeClass('init');
			
			if($(this).val().length > 0) {
				$(this).removeClass('invalid').addClass('valid');
				
				if($(this).attr('id') === 'password-field')
					$('.checkbox-label').removeClass('hidden');
			} else {
				$(this).removeClass('valid').addClass('invalid');
				
				if($(this).attr('id') === 'password-field')
					$('.checkbox-label').addClass('hidden');
			}
		}
	});
	
	/**
	 * Validate a required checkbox field.
	 * @since 2.1.9[a]
	 */
	$('.checkbox-input').on('click', function() {
		if($(this).parent().hasClass('required')) {
			$(this).parent().removeClass('init');
			
			if($(this).prop('checked'))
				$(this).parent().removeClass('invalid').addClass('valid');
			else
				$(this).parent().removeClass('valid').addClass('invalid');
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
		
		if($(form).find('.invalid').length > 0) {
			e.preventDefault();
			
			$(form).find('.required').removeClass('init');
		}
	});
	
	/**
	 * Show hidden conditional fields if appropriate.
	 * @since 1.2.0[b]{ss-04}
	 */
	$('.checkbox-label.conditional-toggle').each(function() {
		if(!$(this).children('input').prop('checked')) $(this).siblings('.conditional-field').addClass('hidden');
	});
	
	$('.checkbox-label.conditional-toggle input').on('change', function() {
		// Fetch the conditional checkbox label
		let self = $(this).parent();
		
		if($(self).children('input').prop('checked'))
			$(self).siblings('.conditional-field').removeClass('hidden');
		else
			$(self).siblings('.conditional-field').addClass('hidden');
	});
	
	/*------------------------------*\
		IMAGES
	\*------------------------------*/
	
	/**
	 * Remove an image.
	 * @since 2.1.5[a]
	 */
	$('.image-remove').on('click', function() {
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
		if(is_dash) {
			let max = $('#max-ct').val();
			
			$('.bar').each(function() {
				let count = $(this).text();
				
				$(this).css({ height: (count / max * 100) + '%' });
			});
		}
	})($('body').hasClass('dashboard'));
	
	/**
	 * Show/hide the record search form.
	 * @since 1.3.7[b]
	 */
	$('#search-toggle').on('click', function() {
		let form = $('.search-form');
		
		$(form).toggleClass('is-visible');
		
		if($(form).hasClass('is-visible'))
			$('#record-search').focus();
	});
	
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
	 * Dismiss a status message.
	 * @since 1.3.8[b]
	 */
	$('.notice .dismiss').on('click', function() {
		$(this).parent().hide(250);
		
		let id = $(this).parent().data('id');
		
		// Permanently dismiss a notice if it has an id
		if(id) {
			let data = {
				'dismiss_notice': true,
				'notice_id': id
			};
			
			$.ajax({
				data: data,
				method: 'POST',
				success: result => {
					console.log(result);
				},
				url: '/admin/includes/ajax.php'
			});
		}
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
		let str = $(this).val();
		
		// Remove special characters
		str = str.replace(/[^\w\s-]/gi, '');
		
		// Replace spaces with hyphens
		str = str.replace(/[\s-]+/gi, '-');
		
		$(this).val(str.toLowerCase());
	});
	
	/**
	 * Format a slug.
	 * @since 2.1.9[a]
	 *
	 * @param string field
	 */
	function formatSlug(field) {
		let str = $(field).val();
		
		str = str.trim();
		
		// Remove special characters
		str = str.replace(/[^\w\s-]/gi, '');
		
		// Replace spaces with hyphens
		str = str.replace(/[\s-]+/gi, '-');
		
		$('#slug-field').val(str.toLowerCase());
		$('#slug-field').removeClass('init');
		
		if($('#slug-field').val().length === 0)
			$('#slug-field').removeClass('valid').addClass('invalid');
		else
			$('#slug-field').removeClass('invalid').addClass('valid');
	}
	
	/**
	 * Select all/none on a checkbox list.
	 * @since 1.2.0[b]
	 */
	$('#select-all').on('click', function() {
		let list = $(this).parents('.checkbox-list');
		
		if($(this).prop('checked')) {
			$(list).find('.checkbox-input').prop('checked', true);
			$(this).siblings('span').text('SELECT NONE');
		} else {
			$(list).find('.checkbox-input').prop('checked', false);
			$(this).siblings('span').text('SELECT ALL');
		}
	});
	
	(function() {
		let list = $('.checkbox-list');
		let all_checked = false;
		
		$(list).find('.checkbox-input').each(function(i) {
			if(i !== 0) {
				if($(this).prop('checked')) {
					all_checked = true;
					return false;
				}
			}
		});
		
		if(all_checked) {
			$('#select-all').prop('checked', true);
			$('#select-all').siblings('span').text('SELECT NONE');
		}
	})();
	
	/**
	 * Event handler to generate a random password.
	 * @since 2.1.9[a]
	 */
	$('#password-gen').on('click', function() {
		$('#password-field').val(generatePassword());
		$('#password-field').removeClass('invalid init').addClass('valid');
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
		
		let password = '';
		
		// Run the generator
		for(let i = 0; i < parseInt(length); i++) {
			let rand = Math.floor(Math.random() * chars.length);
			
			password += chars.substring(rand, rand + 1);
		}
		
		return password;
	}
});