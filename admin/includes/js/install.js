/*!
 * Scripts that run during installation.
 * @since 1.2.6[b]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	$('body').on('submit', '.data-form', function(e) {
		// Prevent the default (submit) action
		e.preventDefault();
		
		// Fetch the page content
		let content = $('.wrapper').html();
		
		// Fetch the field values
		let site_title = $('#site-title').val();
		let username = $('#username').val();
		let password = $('#password').val();
		let admin_email = $('#admin-email').val();
		let do_robots = $('#do-robots').prop('checked');
		
		// Tell the form that it should submit via AJAX
		$('#submit-ajax').val(1);
		
		// Display the spinner
		$('.wrapper').html('<div class="spinner"><i class="fas fa-spinner"></i><br><span>Installing...</span></div>');
		
		// Submit the form data
		$.ajax({
			contentType: false,
			data: new FormData(this),
			method: 'POST',
			processData: false,
			success: result => {
				// Fetch the result
				result = result.split(';');
				
				// Set the error status and message
				let error = result[0];
				let message = result[1];
				
				// Check whether an error was returned
				if(error) {
					// Reset the page content
					$('.wrapper').html(content);
					
					// Populate the field values
					$('#site-title').val(site_title);
					$('#username').val(username);
					$('#password').val(password);
					$('#admin-email').val(admin_email);
					$('#do-robots').prop('checked', do_robots);
					$('#submit-ajax').val(1);
					
					// Check whether a status message is already being displayed
					if($('.status-message').length > 0)
						$('.status-message').text(message);
					else
						$('<p class="status-message failure">' + message + '</p>').insertBefore('.data-form');
				} else {
					// Display the success message
					$('.wrapper').html(message);
				}
			},
			url: '/admin/includes/run-install.php'
		});
	});
});