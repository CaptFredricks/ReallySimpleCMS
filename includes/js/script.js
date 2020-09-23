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
		COMMENTS
	\*------------------------------*/
	
	/**
	 * Upvote a comment.
	 * @since 1.1.0[b]{ss-03}
	 */
	$('.upvote a').on('click', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Create an object to hold the data passed to the server
		let data = {
			'id': $(this).data('id'),
			'vote': $(this).data('vote'),
			'type': 'upvotes'
		};
		
		// Submit the data
		submitVote(data, $(this));
		
		// Check whether the user has already upvoted
		if($(this).data('vote')) {
			// Set the vote status to 'unvoted'
			$(this).data('vote', 0);
		} else {
			// Set the vote status to 'voted'
			$(this).data('vote', 1);
			
			// Fetch the downvote link
			let downvote = $(this).parent().siblings('.downvote').children('a');
			
			// Check whether the user has already downvoted
			if($(downvote).data('vote')) {
				// Submit the data
				submitVote({'id': $(downvote).data('id'), 'vote': $(downvote).data('vote'), 'type': 'downvotes'}, downvote);
				
				// Reset the downvote
				$(downvote).data('vote', 0);
			}
		}
	});
	
	/**
	 * Downvote a comment.
	 * @since 1.1.0[b]{ss-03}
	 */
	$('.downvote a').on('click', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Create an object to hold the data passed to the server
		let data = {
			'id': $(this).data('id'),
			'vote': $(this).data('vote'),
			'type': 'downvotes'
		};
		
		// Submit the data
		submitVote(data, $(this));
		
		// Check whether the user has already downvoted
		if($(this).data('vote')) {
			// Set the vote status to 'unvoted'
			$(this).data('vote', 0);
		} else {
			// Set the vote status to 'voted'
			$(this).data('vote', 1);
			
			// Fetch the upvote link
			let upvote = $(this).parent().siblings('.upvote').children('a');
			
			// Check whether the user has already downvoted
			if($(upvote).data('vote')) {
				// Submit the data
				submitVote({'id': $(upvote).data('id'), 'vote': $(upvote).data('vote'), 'type': 'upvotes'}, upvote);
				
				// Reset the upvote
				$(upvote).data('vote', 0);
			}
		}
	});
	
	/**
	 * Submit the vote via Ajax.
	 * @since 1.1.0[b]{ss-03}
	 */
	function submitVote(data, elem) {
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Update the vote count on the page
				$(elem).siblings('span').text(result);
			},
			url: '/includes/ajax.php'
		});
	}
	
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
			
			// Change the button's title text
			$(this).attr('title', 'Hide password');
			
			// Swap the button's icon
			$(this).children().removeClass('fa-eye').addClass('fa-eye-slash');
		} else if($(this).data('visibility') === 'visible') {
			// Set the password field's type to 'password'
			$('.password-field input').attr('type', 'password');
			
			// Set the visibility to 'hidden'
			$(this).data('visibility', 'hidden');
			
			// Change the button's title text
			$(this).attr('title', 'Show password');
			
			// Swap the button's icon
			$(this).children().removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});
});