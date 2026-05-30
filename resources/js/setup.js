/*!
 * Scripts that run during the setup phase.
 * @since 1.2.6-beta
 *
 * @package ReallySimpleCMS
 */
jQuery(document).ready($ => {
	'use strict';
	
	/**
	 * Run the database installation script.
	 * @since 1.2.6-beta
	 */
	$('body').on('submit', '.data-form', function(e) {
		e.preventDefault();
		
		// Extract the installation path
		let path = window.location.pathname;
		let idx = path.lastIndexOf('/');
		path = path.substring(0, idx);
		
		// Form fields
		let content = $('.content').html();
		let site_title = $('#site-title').val();
		let username = $('#username').val();
		let password = $('#password').val();
		let admin_email = $('#admin-email').val();
		let do_robots = $('#do-robots').prop('checked');
		
		// Tell the form that it should submit via AJAX
		$('#submit-ajax').val(1);
		
		// Display the spinner
		$('.content').html('<div class="spinner"><i class="fa-solid fa-spinner"></i><br><span>Installing...</span></div>');
		
		// Submit the form data
		$.ajax({
			contentType: false,
			data: new FormData(this),
			error: (request, status, error) => {
				console.log(error);
			},
			method: 'POST',
			processData: false,
			success: response => {
				let data = response.split(';');
				let error = data[0];
				let message = data[1];
				
				if(error) {
					// Reset the page content
					$('.content').html(content);
					
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
					$('.content').html(message);
				}
			},
			url: path + '/run-install.php'
		});
	});
});