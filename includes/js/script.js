/**
 * Script file for the front end of the CMS.
 * @since 2.2.1[a]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	/**
	 * Scroll to the top of the page.
	 * @since 2.2.4[a]
	 */
	$(window).on('scroll', function() {
		// Check whether the window has been scrolled at least 250px
		if($(window).scrollTop() > 250) {
			// Add the 'visible' class to the scroll to top button
			$('#scroll-top').addClass('visible');
		} else {
			// Remove the 'visible' class from the scroll to top button
			$('#scroll-top').removeClass('visible');
		}
	});
	
	$('#scroll-top').on('click', function() {
		// Smoothly scroll to the top of the page
		$('html, body').animate({scrollTop: 0}, 750);
	});
	
	/*------------------------------*\
		LOG IN FORM
	\*------------------------------*/
	
	/**
	 * Toggle the visibility of the user's password.
	 * @since 2.2.5[a]
	 */
	$('#password-toggle').on('click', function() {
		// Check whether the password is hidden or visible
		if($(this).data('visibility') === 'hidden') {
			// Set the password field's type to 'text'
			$('.password-field input').attr('type', 'text');
			
			// Set the visibility to 'visible'
			$(this).data('visibility', 'visible');
			
			// Change the title text
			$(this).attr('title', 'Hide password');
			
			// Swap the icon
			$(this).children().removeClass('fa-eye').addClass('fa-eye-slash');
		} else if($(this).data('visibility') === 'visible') {
			// Set the password field's type to 'password'
			$('.password-field input').attr('type', 'password');
			
			// Set the visibility to 'hidden'
			$(this).data('visibility', 'hidden');
			
			// Change the title text
			$(this).attr('title', 'Show password');
			
			// Swap the icon
			$(this).children().removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});
});