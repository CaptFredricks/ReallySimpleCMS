/**
 * Script file for the default front end theme.
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
		// Fetch the current scroll position
		let scroll = getCurrentScroll();
		
		// Toggle the sticky header based on the scroll position
		toggleStickyHeader((scroll > 0));
		
		// Execute an event when the page is scrolled
		$(window).on('scroll', function() {
			// Fetch the current scroll position
			scroll = getCurrentScroll();
			
			// Toggle the sticky header based on the scroll position
			toggleStickyHeader((scroll > 0));
		});
		
		// Fetch and return the current scroll position
		function getCurrentScroll() {
			return window.pageYOffset || document.documentElement.scrollTop;
		}
		
		// Toggle the sticky header
		function toggleStickyHeader(is_sticky = false) {
			// Check whether the header should be sticky
			if(is_sticky) {
				// Add the 'sticky' class to the header
				$('.header').addClass('sticky');
			} else {
				// Remove the 'sticky' class from the header
				$('.header').removeClass('sticky');
			}
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
		// Create a variable to hold the new window width
		let new_width = 0;
		
		// Create a variable to hold the old window width
		let old_width = window.innerWidth;
		
		// Check whether the screen size is mobile
		if(old_width < 1050) doMobile();
		
		// Execute changes on screen resize
		$(window).on('resize', function() {
			// Set the new window width
			new_width = window.innerWidth;
			
			// Check whether the screen size is mobile or desktop
			if(new_width < 1050 && old_width >= 1050)
				doMobile();
			else if(new_width >= 1050 && old_width < 1050)
				undoMobile();
			
			// Set the old window width
			old_width = new_width;
		});
		
		// Convert the menu to mobile view
		function doMobile() {
			// Append toggle buttons to any menu items that have children
			$('.menu-item-has-children').append('<span class="submenu-toggle"><i class="fas fa-chevron-down"></i></span>');
			
			// Execute an event when the nav menu toggle is clicked
			$('.nav-menu-toggle').on('click', function() {
				// Check whether the mobile nav menu is open
				if(!$('.nav-menu-wrap').hasClass('open')) {
					// Set the page body to fixed positioning
					$('body').css('position', 'fixed');
					
					// Add the 'open' class to the nav menu overlay
					$('.nav-menu-overlay').addClass('open');
					
					// Add the 'open' class to the nav menu wrap
					$('.nav-menu-wrap').addClass('open');
					
					// Fade out the nav menu toggle
					$('.nav-menu-toggle .fas').fadeOut(100);
					
					// Switch the nav menu toggle icon
					$('.nav-menu-toggle .fas').removeClass('fa-bars').addClass('fa-times');
					
					// Fade in the nav menu toggle
					$('.nav-menu-toggle .fas').fadeIn(100);
					
					// Add the 'visible' class to the social media widget
					$('.header .social-media').addClass('visible');
				} else {
					// Set the page body to default positioning
					$('body').css('position', '');
					
					// Remove the 'open' class from the nav menu overlay
					$('.nav-menu-overlay').removeClass('open');
					
					// Remove the 'open' class from the nav menu wrap
					$('.nav-menu-wrap').removeClass('open');
					
					// Fade out the nav menu toggle
					$('.nav-menu-toggle .fas').fadeOut(100);
					
					// Switch the nav menu toggle icon
					$('.nav-menu-toggle .fas').removeClass('fa-times').addClass('fa-bars');
					
					// Fade in the nav menu toggle
					$('.nav-menu-toggle .fas').fadeIn(100);
					
					// Remove the 'visible' class from the social media widget
					$('.header .social-media').removeClass('visible');
				}
			});
			
			// Execute an event when the sub menu toggle is clicked
			$('.menu-item-has-children .submenu-toggle').on('click', function() {
				// Check which direction the sub menu toggle icon is facing
				if($(this).children().hasClass('fa-chevron-down')) {
					// Switch the sub menu toggle icon
					$(this).children().removeClass('fa-chevron-down').addClass('fa-chevron-up');
					
					// Display the sub menu
					$(this).siblings('.sub-menu').css('display', 'block');
				} else {
					// Switch the sub menu toggle icon
					$(this).children().removeClass('fa-chevron-up').addClass('fa-chevron-down');
					
					// Hide the sub menu
					$(this).siblings('.sub-menu').css('display', 'none');
				}
			});
		}
		
		// Deconvert the menu from mobile view
		function undoMobile() {
			// Remove all sub menu toggle buttons
			$('.submenu-toggle').remove();
			
			// Clear the nav menu toggle's event handler
			$('.nav-menu-toggle').off('click');
			
			// Clear the sub menu toggle buttons' event handlers
			$('.menu-item-has-children .submenu-toggle').off('click');
			
			// Remove any lingering display value for sub menus
			$('.sub-menu').css('display', '');
		}
	})();
});