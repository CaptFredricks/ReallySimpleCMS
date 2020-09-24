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
	 * Reply to a comment on a comment feed.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('click', '.comment .actions .reply', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Show the reply box and submit button
		$('.comments #comments-reply .textarea-input').show();
		$('.comments #comments-reply .submit-comment').show();
		
		// Remove any status messages
		$('.comments #comments-reply p').remove();
		
		// Scroll to the reply box
		$('html, body').animate({scrollTop: $('.comments').offset().top}, 0);
		
		// Set the replyto value
		$('.comments #comments-reply input[name="replyto"]').val($(this).children().data('replyto'));
	});
	
	/**
	 * Enable and disable the comment submit button.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('input', '.comments .textarea-input', function() {
		// Check whether the field has any data in it
		if($(this).val().length > 0)
			$(this).siblings('.submit-comment').prop('disabled', false);
		else
			$(this).siblings('.submit-comment').prop('disabled', true);
	});
	
	/**
	 * Submit a reply to a comment feed.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('click', '.comments .submit-comment', function() {
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'reply',
			'post': $(this).siblings('input[name="post"]').val(),
			'content': $(this).siblings('.textarea-input').val(),
			'replyto': $(this).siblings('input[name="replyto"]').val()
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Display the result
				$(this).parent().prepend(result);
				
				// Clear the reply box
				$(this).siblings('.textarea-input').val('');
				
				// Hide the reply box and submit button
				$(this).siblings('.textarea-input').hide();
				$(this).hide();
				
				// Refresh the feed
				refreshFeed();
			},
			url: '/includes/ajax.php'
		});
	});
	
	/**
	 * Delete a comment.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('click', '.comment .actions .delete a', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'delete',
			'id': $(this).data('id')
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Refresh the feed
				refreshFeed();
			},
			url: '/includes/ajax.php'
		});
	});
	
	/**
	 * Upvote a comment.
	 * @since 1.1.0[b]{ss-03}
	 */
	$('body').on('click', '.comment .actions .upvote a', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'vote',
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
				submitVote({
					'data_submit': 'vote',
					'id': $(downvote).data('id'),
					'vote': $(downvote).data('vote'),
					'type': 'downvotes'
				}, downvote);
				
				// Reset the downvote
				$(downvote).data('vote', 0);
			}
		}
	});
	
	/**
	 * Downvote a comment.
	 * @since 1.1.0[b]{ss-03}
	 */
	$('body').on('click', '.comment .actions .downvote a', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'vote',
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
				submitVote({
					'data_submit': 'vote',
					'id': $(upvote).data('id'),
					'vote': $(upvote).data('vote'),
					'type': 'upvotes'
				}, upvote);
				
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
		// Submit the data
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
	
	/**
	 * Refresh the comment feed.
	 * @since 1.1.0[b]{ss-04}
	 */
	function refreshFeed() {
		// Remove the comment feed
		$('.comments-wrap').remove();
		
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'refresh',
			'post_slug': $('body').attr('class').split(' ')[0]
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Append the updated feed to the wrapper
				$(result).appendTo('.comments');
			},
			url: '/includes/ajax.php'
		});
	}
	
	/**
	 * Check for feed updates every 120 seconds.
	 * @since 1.1.0[b]{ss-04}
	 */
	let comment_count = 0;
	
	setInterval(function() {
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'checkupdates',
			'post_slug': $('body').attr('class').split(' ')[0]
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Update the comment count if the page has just been loaded
				if(comment_count === 0) comment_count = result;
				
				// Check whether the result differs from the current comment count
				if(result !== comment_count) {
					// Refresh the feed
					refreshFeed();
					
					// Update the comment count
					comment_count = result;
				}
			},
			url: '/includes/ajax.php'
		});
	}, 1000 * 60);
	
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