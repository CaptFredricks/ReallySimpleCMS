/*!
 * Scripts for the front end of the system.
 * @since 2.2.1-alpha
 *
 * @package ReallySimpleCMS
 */
jQuery(document).ready($ => {
	'use strict';
	
	/**
	 * Scroll to the top of the page.
	 * @since 2.2.4-alpha
	 */
	$(window).on('scroll', function() {
		if($(window).scrollTop() > 250)
			$('#scroll-top').addClass('visible');
		else
			$('#scroll-top').removeClass('visible');
	});
	
	$('#scroll-top').on('click', function() {
		$('html, body').animate({ scrollTop: 0 }, 750);
	});
	
	/*------------------------------*\
		COMMENTS
	\*------------------------------*/
	
	let ajax_dir = '/includes/ajax';
	let feed_start, feed_per_page;
	feed_start = feed_per_page = $('.comments .count').data('per-page');
	
	/**
	 * Reply to a comment on a comment feed.
	 * @since 1.1.0-beta_snap-04
	 */
	$('body').on('click', '.comment .actions .reply', function(e) {
		e.preventDefault();
		
		let reply_to = $(this).children().data('replyto');
		
		$('.comments #reply-to').text('Replying to #' + reply_to + ':');
		$('.comments #comments-reply .textarea-input').show();
		$('.comments #comments-reply .submit-comment').show();
		$('.comments #comments-reply p').remove();
		
		$('html, body').animate({ scrollTop: $('.comments').offset().top }, 0);
		$('.comments #comments-reply input[name="replyto"]').val(reply_to);
	});
	
	/**
	 * Enable and disable the comment submit button.
	 * @since 1.1.0-beta_snap-04
	 */
	$('body').on('input', '.comments .textarea-input', function() {
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
	 * @since 1.1.0-beta_snap-04
	 */
	$('body').on('click', '.comments .submit-comment', function() {
		let comments = $('.comments .count').data('comments');
		
		let data = {
			'data_submit': 'reply',
			'post': $(this).siblings('input[name="post"]').val(),
			'content': $(this).siblings('.textarea-input').val(),
			'replyto': $(this).siblings('input[name="replyto"]').val()
		};
		
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				$(this).parent().prepend(result);
				
				$(this).siblings('.textarea-input').val('');
				$(this).siblings('.textarea-input').hide();
				$(this).hide();
				
				$('.comments .count').data('comments', comments + 1);
				
				feed_start++;
				
				refreshFeed();
			},
			url: ajax_dir + '/ajax.php'
		});
	});
	
	/**
	 * Edit a comment.
	 * @since 1.1.0-beta_snap-05
	 */
	$('body').on('click', '.comment .actions .edit a', function(e) {
		e.preventDefault();
		
		let content = $(this).parents().siblings('.content');
		
		$(content).hide();
		
		$(content).after('<div class="textarea-wrap">' +
			'<input type="hidden" name="id" value="' + $(this).data('id') + '">' +
			'<textarea class="textarea-input" cols="60" rows="8">' +
			$(content).text().trim() + '</textarea>' +
			'<button type="button" class="cancel button">Cancel</button>' +
			'<button type="submit" class="update-comment button">Submit</button></div>'
		);
	});
	
	/**
	 * Cancel a comment update.
	 * @since 1.1.0-beta_snap-05
	 */
	$('body').on('click', '.comments .cancel', function() {
		let content = $(this).parent().siblings('.content');
		
		$(content).show();
		$(this).parent().remove();
	});
	
	/**
	 * Submit an updated comment.
	 * @since 1.1.0-beta_snap-05
	 */
	$('body').on('click', '.comments .update-comment', function() {
		let data = {
			'data_submit': 'edit',
			'id': $(this).siblings('input[name="id"]').val(),
			'content': $(this).siblings('.textarea-input').val().trim()
		};
		
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				refreshFeed();
			},
			url: ajax_dir + '/ajax.php'
		});
	});
	
	/**
	 * Delete a comment.
	 * @since 1.1.0-beta_snap-04
	 */
	$('body').on('click', '.comment .actions .delete a', function(e) {
		e.preventDefault();
		
		let comments = $('.comments .count').data('comments');
		
		let data = {
			'data_submit': 'delete',
			'id': $(this).data('id')
		};
		
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				$('.comments .count').data('comments', comments - 1);
				
				feed_start--;
				
				refreshFeed();
			},
			url: ajax_dir + '/ajax.php'
		});
	});
	
	/**
	 * Upvote a comment.
	 * @since 1.1.0-beta_snap-03
	 */
	$('body').on('click', '.comment .actions .upvote a', function(e) {
		e.preventDefault();
		
		let data = {
			'data_submit': 'vote',
			'id': $(this).data('id'),
			'author': $(this).data('author'),
			'vote': $(this).data('vote'),
			'type': 'upvotes'
		};
		
		submitVote(data, $(this));
		
		let downvote = $(this).parent().siblings('.downvote').children('a');
		
		if($(this).data('vote')) {
			$(this).data('vote', 0);
			$(downvote).addClass('active');
		} else {
			$(this).data('vote', 1);
			$(this).addClass('active');
			$(downvote).removeClass('active');
			
			if($(downvote).data('vote')) {
				submitVote({
					'data_submit': 'vote',
					'id': $(downvote).data('id'),
					'vote': $(downvote).data('vote'),
					'type': 'downvotes'
				}, downvote);
				
				$(downvote).data('vote', 0);
			}
		}
	});
	
	/**
	 * Downvote a comment.
	 * @since 1.1.0-beta_snap-03
	 */
	$('body').on('click', '.comment .actions .downvote a', function(e) {
		e.preventDefault();
		
		let data = {
			'data_submit': 'vote',
			'id': $(this).data('id'),
			'author': $(this).data('author'),
			'vote': $(this).data('vote'),
			'type': 'downvotes'
		};
		
		submitVote(data, $(this));
		
		let upvote = $(this).parent().siblings('.upvote').children('a');
		
		if($(this).data('vote')) {
			$(this).data('vote', 0);
			$(upvote).addClass('active');
		} else {
			$(this).data('vote', 1);
			$(this).addClass('active');
			$(upvote).removeClass('active');
			
			if($(upvote).data('vote')) {
				submitVote({
					'data_submit': 'vote',
					'id': $(upvote).data('id'),
					'vote': $(upvote).data('vote'),
					'type': 'upvotes'
				}, upvote);
				
				$(upvote).data('vote', 0);
			}
		}
	});
	
	/**
	 * Submit the vote via Ajax.
	 * @since 1.1.0-beta_snap-03
	 *
	 * @param object $data
	 * @param object $elem
	 * @return undefined
	 */
	function submitVote(data, elem) {
		if(data.author === 1) {
			alert('You cannot vote for your own comment!');
		} else {
			$.ajax({
				data: data,
				method: 'POST',
				success: result => {
					$(elem).siblings('span').text(result);
				},
				url: ajax_dir + '/ajax.php'
			});
		}
	}
	
	/**
	 * Load more comments.
	 * @since 1.2.2-beta
	 */
	$('body').on('click', '.comments .load.button', function() {
		let data = {
			'data_submit': 'load',
			'post_slug': $('body').attr('class').split(' ')[1],
			'start': feed_start,
			'count': feed_per_page
		};
		
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				$('.comments .count').remove();
				$('.comments .load.button').remove();
				$(result).appendTo('.comments-wrap');
				
				feed_start += feed_per_page;
			},
			url: ajax_dir + '/ajax.php'
		});
	});
	
	/**
	 * Refresh the comment feed.
	 * @since 1.1.0-beta_snap-04
	 *
	 * @return undefined
	 */
	function refreshFeed() {
		let comments = $('.comments .count').data('comments');
		
		$('.comments-wrap').empty();
		
		let data = {
			'data_submit': 'refresh',
			'post_slug': $('body').attr('class').split(' ')[1],
			'start': 0,
			'count': comments
		};
		
		$.ajax({
			data: data,
			method: 'POST',
			success: result => {
				$(result).appendTo('.comments-wrap');
			},
			url: ajax_dir + '/ajax.php'
		});
	}
	
	/**
	 * Check for feed updates every 15 seconds.
	 * @since 1.1.0-beta_snap-04
	 */
	if($('.comments-wrap').children().length - 1 > 0) {
		let comment_count = 0;
		
		setInterval(function() {
			let comments = $('.comments .count').data('comments');
			
			let data = {
				'data_submit': 'checkupdates',
				'post_slug': $('body').attr('class').split(' ')[1]
			};
			
			$.ajax({
				data: data,
				method: 'POST',
				success: result => {
					if(comment_count === 0) comment_count = result;
					
					if(result !== comment_count) {
						if(result < comment_count) {
							$('.comments .count').data('comments', comments - 1);
							
							feed_start--;
						} else {
							$('.comments .count').data('comments', comments + 1);
							
							feed_start++;
						}
						
						refreshFeed();
						
						comment_count = result;
					}
				},
				url: ajax_dir + '/ajax.php'
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
	 * @since 2.2.5-alpha
	 */
	$('#password-toggle').on('click', function() {
		if($(this).data('visibility') === 'hidden') {
			$('.password-field input').attr('type', 'text');
			$(this).data('visibility', 'visible');
			$(this).attr('title', 'Hide password');
			$(this).children().removeClass('fa-eye').addClass('fa-eye-slash');
		} else if($(this).data('visibility') === 'visible') {
			$('.password-field input').attr('type', 'password');
			$(this).data('visibility', 'hidden');
			$(this).attr('title', 'Show password');
			$(this).children().removeClass('fa-eye-slash').addClass('fa-eye');
		}
	});
});