/*!
 * Scripts for admin modal windows.
 * @since 2.1.1[a]
 */
jQuery(document).ready($ => {
	// Turn on strict mode
	'use strict';
	
	// Create a variable to hold the clicked modal launch button
	let clicked_button = null;
	
	/**
	 * Launch a modal window.
	 * @since 2.1.1[a]
	 */
	$('.modal-launch').on('click', function() {
		// Set the clicked button to the button that was clicked
		clicked_button = this;
		
		$('body').addClass('modal-open');
		$('.modal').fadeIn(100);
		$('.modal').addClass('in');
		
		// Fetch the type of media that should display in the media library tab
		$('#media-type').text($(this).data('type'));
		
		// Load the media library
		$('.media-wrap').load($('.tabber #media.tab').children().data('href') + '?media_type=' + $('#media-type').text());
		
		// Check whether the clicked button is meant to insert media into the post content
		if($(this).data('insert') === true) {
			// Set the 'Select Media' button to insert a selected media item
			$('#media-select').data('insert', true);
		}
	});
	
	/**
	 * Close an open modal window.
	 * @since 2.1.1[a]
	 */
	$('#modal-close').on('click', function() {
		modalClose();
	});
	
	/**
	 * Delete a specified item from the database.
	 * @since 2.1.8[a]
	 */
	$('.delete-item').on('click', function(e) {
		e.preventDefault();
		
		// Replace the default warning text with the appropriate item type
		$('.delete-wrap h2 span').text($(this).data('item'));
		
		// Fetch the delete link from the data table and link the 'Confirm Delete' button to it
		$('#confirm-delete').attr('href', $(this).attr('href'));
	});
	
	/**
	 * Switch the modal tabs.
	 * @since 2.1.1[a]
	 */
	$('.modal-header .tabber .tab').on('click', function() {
		// Check whether the clicked tab is active
		if(!$(this).hasClass('active')) {
			$('.modal-header .tabber .tab').toggleClass('active');
			$('.modal-body .tab').toggleClass('active');
			
			// Check which tab is now active
			if($('#upload').hasClass('active')) {
				// Clear the upload result
				$('.upload-result').empty();
				
				// Reset the upload form
				$('#media-upload').trigger('reset');
				
				// Disable the 'Select Media' button
				$('#media-select').prop('disabled', true);
			} else if($('#media').hasClass('active')) {
				// Empty the media tab
				$('.media-wrap').empty();
				
				// Load the media library
				$('.media-wrap').load($(this).children().data('href') + '?media_type=' + $('#media-type').text());
				
				// Clear the media details
				$('.media-details .field').empty();
				
				// Disable the 'Select Media' button
				$('#media-select').prop('disabled', true);
			}
		}
	});
	
	/**
	 * Submit the upload form.
	 * @since 2.1.6[a]
	 */
	$('#media-upload').on('submit', function(e) {
		e.preventDefault();
		
		$.ajax({
			contentType: false,
			data: new FormData(this),
			method: 'POST',
			processData: false,
			success: result => {
				// Display the result
				$('.upload-result').html(result);
				
				// Check whether the upload was successful
				if(result.indexOf('success') !== -1) {
					// Enable the 'Select Media' button
					$('#media-select').prop('disabled', false);
				}
			},
			url: $(this).attr('action')
		});
	});
	
	/**
	 * Select a media item.
	 * @since 2.1.2[a]
	 */
	$(document).on('click', '.media-item', function() {
		// Check whether the clicked item is already selected
		if(!$(this).hasClass('selected')) {
			// Remove the 'selected' class from all other items
			$('.media-item').removeClass('selected');
			
			// Add the 'selected' class
			$(this).addClass('selected');
			
			// Fetch the media item's hidden fields
			let fields = $(this).find('.hidden');
			
			// Create a variable to hold each field's name
			let field = '';
			
			// Loop through the fields
			$(fields).each(function() {
				// Fetch the field's name
				field = $(this).data('field');
				
				// Populate each field in the details section
				$('.media-details .' + field).html($(this).html());
			});
			
			// Enable the 'Select Media' button
			$('#media-select').prop('disabled', false);
		} else {
			$(this).removeClass('selected');
			
			// Clear the media details
			$('.media-details .field').empty();
			
			// Disable the 'Select Media' button
			$('#media-select').prop('disabled', true);
		}
	});
	
	/**
	 * Select and insert media (via upload or media library).
	 * @since 2.1.3[a]
	 */
	$('#media-select').on('click', function() {
		let data = { id: 0, title: '', filepath: '', mime_type: '', alt_text: '', width: 0 };
		
		// Check which tab is active
		if($('#upload').hasClass('active')) {
			// Check whether the hidden fields are in the result
			if($('.upload-result .hidden[data-field="id"]').length && $('.upload-result .hidden[data-field="filepath"]').length) {
				// Populate the data object
				data.id = $('.upload-result .hidden[data-field="id"]').text();
				data.title = $('.upload-result .hidden[data-field="title"]').text();
				data.filepath = $('.upload-result .hidden[data-field="filepath"]').text();
				data.mime_type = $('.upload-result .hidden[data-field="mime_type"]').text();
				data.width = $('.upload-result .hidden[data-field="width"]').text();
				
				// Check whether the uploaded media should be inserted into post content
				if($(this).data('insert') === true) {
					// Insert the media
					insertMedia($('.content .textarea-input[name="content"]'), data);
				} else {
					// Insert the media's id onto the form
					$(clicked_button).siblings('input[data-field="id"]').val(data.id);
					
					// Insert the media's filepath onto the form
					$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').attr('src', data.filepath);
				}
			}
		} else if($('#media').hasClass('active')) {
			// Check whether a media item has been selected
			if($('.media-item').hasClass('selected')) {
				// Populate the data object
				data.id = $('.media-item.selected .hidden[data-field="id"]').text();
				data.title = $('.media-item.selected .hidden[data-field="title"]').text();
				data.filepath = $('.media-item.selected .hidden[data-field="filepath"] a').attr('href');
				data.mime_type = $('.media-item.selected .hidden[data-field="mime_type"]').text();
				data.alt_text = $('.media-item.selected .hidden[data-field="alt_text"]').text();
				data.width = $('.media-item.selected .hidden[data-field="width"]').text();
				
				// Check whether the selected media should be inserted into post content
				if($(this).data('insert') === true) {
					// Insert the media
					insertMedia($('.content .textarea-input[name="content"]'), data);
				} else {
					// Insert the media's id on the form
					$(clicked_button).siblings('input[data-field="id"]').val(data.id);
					
					// Insert the media's filepath on the form
					$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').attr('src', data.filepath);
				}
			} else {
				// Set the media's 'id' field to zero
				$(clicked_button).siblings('input[data-field="id"]').val(0);
				
				// Set the media's thumbnail to an empty value
				$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').attr('src', '//:0');
			}
		}
		
		// Check whether the thumbnail's source points to an image
		if($(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').attr('src') !== '//:0' && $(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').attr('src') !== '') {
			// Set the image's max width to 'none'
			$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').css({maxWidth: 'none'});
			
			// Display the image
			$(clicked_button).siblings('.image-wrap').addClass('visible');
			
			// Remove the greyed out effect from the media thumbnail
			$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').removeClass('greyout');
			
			// Set the image wrap's width based on the image's width
			$(clicked_button).siblings('.image-wrap').width(data.width);
			
			// Set the image's max width to '100%'
			$(clicked_button).siblings('.image-wrap').children('img[data-field="thumb"]').css({maxWidth: '100%'});
		} else {
			// Hide the image
			$(clicked_button).siblings('.image-wrap').removeClass('visible');
		}
		
		modalClose();
	});
	
	/**
	 * Insert a media item into post content.
	 * @since 2.1.10[a]
	 *
	 * @param object container
	 * @param object data
	 */
	function insertMedia(container, data) {
		// Fetch the text of the container element
		let text = $(container).val();
		
		// Create an object to hold the container's content data
		let content = {
			selection_start: $(container).prop('selectionStart'),
			selection_end: $(container).prop('selectionEnd'),
			text_before: '',
			text_after: ''
		};
		
		// Fetch the text before the selection
		content.text_before = text.substring(0, content.selection_start);
		
		// Fetch the text after the selection
		content.text_after = text.substring(content.selection_end, text.length);
		
		// Create an empty variable to hold the media element
		let media = '';
		
		// Determine what kind of HTML tag to construct based on the media's MIME type
		if(data.mime_type.indexOf('image') !== -1) {
			// Construct an image tag
			media = '<img src="' + data.filepath + '" alt="' + (data.hasOwnProperty('alt_text') ? data.alt_text : '') + '">';
		} else if(data.mime_type.indexOf('audio') !== -1) {
			// Construct an audio tag
			media = '<audio src="' + data.filepath + '"></audio>';
		} else if(data.mime_type.indexOf('video') !== -1) {
			// Construct a video tag
			media = '<video src="' + data.filepath + '"></video>';
		} else {
			// Construct an anchor tag
			media = '<a href="' + data.filepath + '">' + data.title + '</a>';
		}
		
		// Update the container's content
		$(container).val(content.text_before + media + content.text_after);
	}
	
	/**
	 * Close an open modal and perform cleanup.
	 * @since 2.1.3[a]
	 */
	function modalClose() {
		$('body').removeClass('modal-open');
		
		// Hide the modal
		$('.modal').fadeOut(500);
		
		$('.modal').removeClass('in');
		
		// Check whether the open modal is the upload modal
		if($('.modal').attr('id') === 'modal-upload') {
			// Clear the upload result
			$('.upload-result').empty();
			
			// Reset the upload form
			$('#media-upload').trigger('reset');
			
			// Empty the media tab
			$('.media-wrap').empty();
			
			// Clear the media details
			$('.media-details .field').empty();
			
			// Disable the 'Select Media' button
			$('#media-select').prop('disabled', true);
		}
		
		// Unset the media insert data
		$('#media-select').data('insert', false);
	}
});