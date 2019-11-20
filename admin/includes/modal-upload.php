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
				<form action="<?php echo ADMIN.'/upload.php'; ?>" method="post" enctype="multipart/form-data">
					<input type="file" class="" name="media_upload">
					<input type="hidden" id="">
					<input type="submit" class="submit-input button" name="upload_submit" value="Upload">
				</form>
			</div>
			<div class="tab clear" data-tab="media">
				<div class="media-wrap clear"></div>
				<div class="media-details">
					<h2 class="title"></h2>
					
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" id="media-select" class="button">Select</button>
		</div>
	</div>
</div>
<?php
// Include the modal scripts
getAdminScript('modal.js');