/**
 * Script file for the Carbon theme.
 * @since 2.2.2[a]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	/*------------------------------*\
		SCROLLING
	\*------------------------------*/
	
	/**
	 * Make the header sticky on page scroll.
	 * @since 2.2.2[a]
	 */
	(() => {
		let scroll = getCurrentScroll();
		
		toggleStickyHeader((scroll > 0));
		
		$(window).on('scroll', function() {
			scroll = getCurrentScroll();
			toggleStickyHeader((scroll > 0));
		});
		
		function getCurrentScroll() {
			return window.pageYOffset || document.documentElement.scrollTop;
		}
		
		function toggleStickyHeader(is_sticky = false) {
			if(is_sticky)
				$('.header').addClass('sticky');
			else
				$('.header').removeClass('sticky');
		}
	})();
	
	/*------------------------------*\
		MOBILE RESPONSIVENESS
	\*------------------------------*/
	
	/**
	 * Make the header and nav menu mobile responsive.
	 * @since 2.2.1[a]
	 */
	(() => {
		let new_width = 0;
		let old_width = window.innerWidth;
		let breakpoint = 1050;
		
		if(old_width < breakpoint) doMobile();
		
		$(window).on('resize', function() {
			new_width = window.innerWidth;
			
			// Check whether the screen size is mobile or desktop
			if(new_width < breakpoint && old_width >= breakpoint)
				doMobile();
			else if(new_width >= breakpoint && old_width < breakpoint)
				undoMobile();
			
			old_width = new_width;
		});
		
		// Convert the menu to mobile view
		function doMobile() {
			// Append toggle buttons to any menu items that have children
			$('.menu-item-has-children').append('<span class="submenu-toggle"><i class="fa-solid fa-chevron-down"></i></span>');
			
			$('.nav-menu-toggle').on('click', function() {
				if(!$('.nav-menu-wrap').hasClass('open')) {
					$('body').css('position', 'fixed');
					$('.nav-menu-overlay').addClass('open');
					$('.nav-menu-wrap').addClass('open');
					$('.nav-menu-toggle .fa-solid').fadeOut(100);
					$('.nav-menu-toggle .fa-solid').removeClass('fa-bars').addClass('fa-xmark');
					$('.nav-menu-toggle .fa-solid').fadeIn(100);
					$('.header .social-media').addClass('visible');
				} else {
					$('body').css('position', '');
					$('.nav-menu-overlay').removeClass('open');
					$('.nav-menu-wrap').removeClass('open');
					$('.nav-menu-toggle .fa-solid').fadeOut(100);
					$('.nav-menu-toggle .fa-solid').removeClass('fa-xmark').addClass('fa-bars');
					$('.nav-menu-toggle .fa-solid').fadeIn(100);
					$('.header .social-media').removeClass('visible');
				}
			});
			
			$('.menu-item-has-children .submenu-toggle').on('click', function() {
				if($(this).children().hasClass('fa-chevron-down')) {
					// Show the submenu
					$(this).children().removeClass('fa-chevron-down').addClass('fa-chevron-up');
					$(this).siblings('.sub-menu').css('display', 'block');
				} else {
					// Hide the submenu
					$(this).children().removeClass('fa-chevron-up').addClass('fa-chevron-down');
					$(this).siblings('.sub-menu').css('display', 'none');
				}
			});
		}
		
		// Deconvert the menu from mobile view
		function undoMobile() {
			$('.submenu-toggle').remove();
			$('.nav-menu-toggle').off('click');
			$('.menu-item-has-children .submenu-toggle').off('click');
			$('.sub-menu').css('display', '');
		}
	})();
	
	/*------------------------------*\
		COMMENTS
	\*------------------------------*/
	
	/**
	 * Reply to a comment on a comment feed.
	 * @since 1.1.0[b]{ss-05}
	 */
	$('body').off('click', '.comment .actions .reply');
	$('body').on('click', '.comment .actions .reply', function(e) {
		e.preventDefault();
		
		let reply_to = $(this).children().data('replyto');
		
		$('.comments #reply-to').text('Replying to #' + reply_to + ':');
		$('.comments #comments-reply .textarea-input').show();
		$('.comments #comments-reply .submit-comment').show();
		$('.comments #comments-reply p').remove();
		$('html, body').animate({scrollTop: $('.comments').offset().top - 50}, 0);
		$('.comments #comments-reply input[name="replyto"]').val(reply_to);
	});
	
	/**
	 * Scroll to a parent comment.
	 * @since 1.1.0[b]{ss-05}
	 */
	$('body').on('click', '.comment .meta .replyto a', function(e) {
		e.preventDefault();
		
		let anchor_link = $(this).attr('href');
		let anchor = anchor_link.substring(anchor_link.indexOf('#'));
		
		if($(anchor).length)
			$('html, body').animate({scrollTop: $(anchor).offset().top - 100}, 0);
	});
	
	/**
	 * Scroll to a linked comment.
	 * @since 1.1.0[b]
	 */
	$(window).on('hashchange', function() {
		let anchor = window.location.hash;
		
		$('html, body').animate({scrollTop: $(anchor).offset().top - 100}, 0);
	});
	
	if(window.location.hash) {
		let anchor = window.location.hash;
		
		$('html, body').animate({scrollTop: $(anchor).offset().top - 100}, 0);
	}
});