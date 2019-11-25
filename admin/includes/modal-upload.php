<div id="modal-upload" class="modal fade">
	<div class="modal-wrap">
		<div class="modal-header">
			<ul class="tabber">
				<li id="upload" class="tab active">
					<a href="javascript:void(0)">Upload</a>
				</li>
				<li id="media" class="tab">
					<a href="javascript:void(0)" data-href="<?php echo ADMIN.'/load-media.php'; ?>">Media</a>
				</li>
			</ul>
			<button type="button" id="modal-close">
				<i class="fas fa-times"></i>
			</button>
		</div>
		<div class="modal-body">
			<div class="tab active" data-tab="upload">
				<div class="upload-wrap">
					<h2>Select a file to upload</h2>
					<div class="upload-result"></div>
					<form id="media-upload" action="<?php echo ADMIN.'/upload.php'; ?>" method="post" enctype="multipart/form-data">
						<input type="file" name="media_upload">
						<input type="submit" class="submit-input button" name="upload_submit" value="Upload">
					</form>
					<p>Maximum upload size: <?php echo getFileSize(getSizeInBytes(ini_get('upload_max_filesize')), 0); ?></p>
				</div>
			</div>
			<div class="tab" data-tab="media">
				<div class="media-wrap"></div>
				<div class="media-details">
					<h2>Details</h2>
					<div class="info">
						<div class="field thumb"></div>
						<div class="field title"></div>
						<div class="field filename"></div>
						<div class="field date"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" id="media-select" class="button">Select Media</button>
		</div>
	</div>
</div>
<?php
// Include the modal scripts
getAdminScript('modal.js');