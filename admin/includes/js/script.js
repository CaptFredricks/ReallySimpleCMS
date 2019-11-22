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
	
	/**
	 * Display a post's featured image.
	 * @since 2.1.4[a]
	 */
	$('#media-select').on('click', function() {
		// Check whether the thumbnail's source points to an image
		if($('#media-thumb').attr('src') !== '//:0' && $('#media-thumb').attr('src') !== '') {
			// Display the featured image
			$('.feat-image-wrap').show();
		} else {
			// Hide the featured image
			$('.feat-image-wrap').hide();
		}
	});
});