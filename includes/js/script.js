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
	
	// Set the initial comment starting value
	let feed_start = 10;
	
	// Set the initial comment count value
	let feed_count = 10;
	
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
		if($(this).val().length > 0) {
			$(this).siblings('.submit-comment').prop('disabled', false);
			$(this).siblings('.update-comment').prop('disabled', false);
		} else {
			$(this).siblings('.submit-comment').prop('disabled', true);
			$(this).siblings('.update-comment').prop('disabled', true);
		}
	});
	
	/**
	 * Submit a reply to a comment feed.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('click', '.comments .submit-comment', function() {
		// Fetch the number of comments in the feed
		let comments = $('.comments .count').data('comments');
		
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
				
				// Update the number of comments in the feed
				$('.comments .count').data('comments', comments + 1);
				
				// Update the comment starting value
				feed_start++;
				
				// Refresh the feed
				refreshFeed();
			},
			url: '/includes/ajax.php'
		});
	});
	
	/**
	 * Edit a comment.
	 * @since 1.1.0[b]{ss-05}
	 */
	$('body').on('click', '.comment .actions .edit a', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Fetch the comment's content
		let content = $(this).parents().siblings('.content');
		
		// Hide the comment
		$(content).hide();
		
		// Insert a textarea for editing the comment's content
		$(content).after('<div class="textarea-wrap">' +
						 '<input type="hidden" name="id" value="' + $(this).data('id') + '">' +
						 '<textarea class="textarea-input" cols="60" rows="8">' +
						 $(content).text().trim() + '</textarea>' +
						 '<button type="button" class="cancel button">Cancel</button>' +
						 '<button type="submit" class="update-comment button">Submit</button></div>');
	});
	
	/**
	 * Cancel a comment update.
	 * @since 1.1.0[b]{ss-05}
	 */
	$('body').on('click', '.comments .cancel', function() {
		// Fetch the comment's content
		let content = $(this).parent().siblings('.content');
		
		// Show the comment
		$(content).show();
		
		// Remove the textarea
		$(this).parent().remove();
	});
	
	/**
	 * Submit an updated comment.
	 * @since 1.1.0[b]{ss-05}
	 */
	$('body').on('click', '.comments .update-comment', function() {
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'edit',
			'id': $(this).siblings('input[name="id"]').val(),
			'content': $(this).siblings('.textarea-input').val().trim()
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
	 * Delete a comment.
	 * @since 1.1.0[b]{ss-04}
	 */
	$('body').on('click', '.comment .actions .delete a', function(e) {
		// Prevent the default action
		e.preventDefault();
		
		// Fetch the number of comments in the feed
		let comments = $('.comments .count').data('comments');
		
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
				// Update the number of comments in the feed
				$('.comments .count').data('comments', comments - 1);
				
				// Update the comment starting value
				feed_start--;
				
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
		
		// Fetch the downvote link
		let downvote = $(this).parent().siblings('.downvote').children('a');
		
		// Check whether the user has already upvoted
		if($(this).data('vote')) {
			// Set the vote status to 'unvoted'
			$(this).data('vote', 0);
			
			// Add the downvote's 'active' class
			$(downvote).addClass('active');
		} else {
			// Set the vote status to 'voted'
			$(this).data('vote', 1);
			
			// Add the 'active' class
			$(this).addClass('active');
			
			// Remove the downvote's 'active' class
			$(downvote).removeClass('active');
			
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
		
		// Fetch the upvote link
		let upvote = $(this).parent().siblings('.upvote').children('a');
		
		// Check whether the user has already downvoted
		if($(this).data('vote')) {
			// Set the vote status to 'unvoted'
			$(this).data('vote', 0);
			
			// Add the upvote's 'active' class
			$(upvote).addClass('active');
		} else {
			// Set the vote status to 'voted'
			$(this).data('vote', 1);
			
			// Add the 'active' class
			$(this).addClass('active');
			
			// Remove the upvote's 'active' class
			$(upvote).removeClass('active');
			
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
	 *
	 * @param object $data
	 * @param object $elem
	 * @return undefined
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
	 * Load more comments.
	 * @since 1.2.2[b]
	 */
	$('body').on('click', '.comments .load.button', function() {
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'load',
			'post_slug': $('body').attr('class').split(' ')[1],
			'start': feed_start,
			'count': feed_count
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Remove the comments count
				$('.comments .count').remove();
				
				// Remove the 'load more' button
				$('.comments .load.button').remove();
				
				// Append the updated feed to the wrapper
				$(result).appendTo('.comments-wrap');
				
				// Update the starting comment value
				feed_start += 10;
			},
			url: '/includes/ajax.php'
		});
	});
	
	/**
	 * Refresh the comment feed.
	 * @since 1.1.0[b]{ss-04}
	 *
	 * @return undefined
	 */
	function refreshFeed() {
		// Fetch the number of comments in the feed
		let comments = $('.comments .count').data('comments');
		
		// Empty the comment feed
		$('.comments-wrap').empty();
		
		// Create an object to hold the data passed to the server
		let data = {
			'data_submit': 'refresh',
			'post_slug': $('body').attr('class').split(' ')[1],
			'start': 0,
			'count': comments
		};
		
		// Submit the data
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				// Append the updated feed to the wrapper
				$(result).appendTo('.comments-wrap');
			},
			url: '/includes/ajax.php'
		});
	}
	
	/**
	 * Check for feed updates every 15 seconds.
	 * @since 1.1.0[b]{ss-04}
	 */
	if($('.comments').length) {
		// Initialize the comment count to zero
		let comment_count = 0;
		
		setInterval(function() {
			// Fetch the number of comments in the feed
			let comments = $('.comments .count').data('comments');
			
			// Create an object to hold the data passed to the server
			let data = {
				'data_submit': 'checkupdates',
				'post_slug': $('body').attr('class').split(' ')[1]
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
						// Check whether the result is less than the current comment count
						if(result < comment_count) {
							// Update the number of comments in the feed
							$('.comments .count').data('comments', comments - 1);
							
							// Update the comment starting value
							feed_start--;
						} else {
							// Update the number of comments in the feed
							$('.comments .count').data('comments', comments + 1);
							
							// Update the comment starting value
							feed_start++;
						}
						
						// Refresh the feed
						refreshFeed();
						
						// Update the comment count
						comment_count = result;
					}
				},
				url: '/includes/ajax.php'
			});
		}, 1000 * 15);
	}
	
	/*------------------------------*\
		LOG IN FORM
	\*------------------------------*/
	
	let field_height = $('.password-field input').height();
	let button_height = $('#password-toggle').height();
	
	// Correct the password toggle button's height if it's different from the password field's height
	if(button_height < field_height)
		$('#password-toggle').css({ height: field_height + 12 + 'px' });
	
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