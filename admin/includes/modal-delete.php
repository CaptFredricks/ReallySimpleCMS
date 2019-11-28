<div id="modal-delete" class="modal fade">
	<div class="modal-wrap">
		<div class="modal-header clear">
			<button type="button" id="modal-close">
				<i class="fas fa-times"></i>
			</button>
		</div>
		<div class="modal-body">
			<div class="delete-wrap">
				<h2>WARNING: Are you sure you wish to delete this <span>item</span>?</h2>
				<h3>It will be lost forever if you proceed.</h3>
			</div>
		</div>
		<div class="modal-footer">
			<a id="confirm-delete" class="button" href="">Confirm Delete</a>
		</div>
	</div>
</div>
<?php
// Include the modal scripts
getAdminScript('modal.js');