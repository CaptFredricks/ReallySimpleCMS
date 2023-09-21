/*!
 * Scripts that run during installation.
 * @since 1.2.6[b]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	/**
	 * Run the database installation.
	 * @since 1.2.6[b]
	 */
	$('body').on('submit', '.data-form', function(e) {
		e.preventDefault();
		
		let content = $('.wrapper').html();
		let site_title = $('#site-title').val();
		let username = $('#username').val();
		let password = $('#password').val();
		let admin_email = $('#admin-email').val();
		let do_robots = $('#do-robots').prop('checked');
		
		// Tell the form that it should submit via AJAX
		$('#submit-ajax').val(1);
		
		// Display the spinner
		$('.wrapper').html('<div class="spinner"><i class="fa-solid fa-spinner"></i><br><span>Installing...</span></div>');
		
		// Submit the form data
		$.ajax({
			contentType: false,
			data: new FormData(this),
			error: (request, status, error) => {
				console.log(error);
			},
			method: 'POST',
			processData: false,
			success: result => {
				result = result.split(';');
				
				let error = result[0];
				let message = result[1];
				
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
					
					if($('.status-message').length > 0)
						$('.status-message').text(message);
					else
						$('<p class="status-message failure">' + message + '</p>').insertBefore('.data-form');
				} else {
					$('.wrapper').html(message);
				}
			},
			url: '/admin/includes/run-install.php'
		});
	});
});