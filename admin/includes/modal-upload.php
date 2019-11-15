<div id="modal-upload" class="modal fade">
	<div class="modal-wrap">
		<div class="modal-header">
			<ul class="tabber">
				<li id="upload-tab" class="active">
					<a href="javascript:void(0)">Upload</a>
				</li>
				<li id="media-tab">
					<a href="javascript:void(0)" data-href="<?php echo ADMIN.'/load-media.php'; ?>">Media</a>
				</li>
			</ul>
			<button type="button" id="modal-close">
				<i class="fas fa-times"></i>
			</button>
		</div>
		<div class="modal-body">
			abc
		</div>
		<div class="modal-footer">
			<button type="button" id="media-select" class="button">Select</button>
		</div>
	</div>
</div>
<?php
// Include the modal scripts
getAdminScript('modal.js');