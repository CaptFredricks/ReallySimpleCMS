<?php
/**
 * Upload modal for admin pages.
 * @since 2.1.1-alpha
 *
 * @package ReallySimpleCMS
 */
?>
<div id="modal-upload" class="modal fade">
	<div class="modal-wrap">
		<div class="modal-header">
			<ul class="tabber">
				<?php
				// Upload tab
				domTagPr('li', array(
					'id' => 'upload',
					'class' => 'tab active',
					'content' => domTag('a', array(
						'href' => 'javascript:void(0)',
						'content' => 'Upload'
					))
				));
				
				// Media tab
				domTagPr('li', array(
					'id' => 'media',
					'class' => 'tab',
					'content' => domTag('a', array(
						'href' => 'javascript:void(0)',
						'data-href' => AJAX . '/load-media.php',
						'content' => 'Media'
					))
				));
				?>
			</ul>
			<button type="button" id="modal-close">
				<i class="fa-solid fa-xmark"></i>
			</button>
		</div>
		<div class="modal-body">
			<div class="tab active" data-tab="upload">
				<div class="upload-wrap">
					<h2>Select a file to upload</h2>
					<div class="upload-result"></div>
					<form id="media-upload" action="<?php echo AJAX . '/file-upload.php'; ?>" method="post" enctype="multipart/form-data">
						<input type="file" name="media_upload">
						<input type="submit" class="submit-input button" name="upload_submit" value="Upload">
					</form>
					<p>Maximum upload size: <?php echo getFileSize(getSizeInBytes(ini_get('upload_max_filesize')), 0); ?></p>
				</div>
			</div>
			<div class="tab" data-tab="media">
				<div id="media-type" class="hidden"></div>
				<div class="media-wrap"></div>
				<div class="media-details">
					<h2>Details</h2>
					<div class="info">
						<div class="field thumb"></div>
						<div class="field title"></div>
						<div class="field filepath"></div>
						<div class="field date"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" id="media-select" class="button" disabled>Select Media</button>
		</div>
	</div>
</div>
<?php
putScript('modal' . (isDebugMode() ? '' : '.min') . '.js');