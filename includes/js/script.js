/**
 * Script file for the front end of the CMS.
 * @since 2.2.1[a]
 */
jQuery(document).ready(function($) {
	// Turn on strict mode
	'use strict';
	
	/**
	 * Scroll to the top of the page.
	 * @since 2.2.4[a]
	 */
	(() => {
		// Execute an event when the page is scrolled
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
		
		// Execute an event when the scroll to top button is clicked
		$('#scroll-top').on('click', function() {
			// Smoothly scroll to the top of the page
			$('html, body').animate({scrollTop: 0}, 950);
		});
	})();
});