/*!
 * Scripts for the admin dashboard.
 * @since 1.5.6[a]
 */
jQuery(document).ready(function($) {
	// Turn on strict mode
	'use strict';
	
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
});