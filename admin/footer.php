<?php
/**
 * Admin dashboard footer.
 * @since 1.0.2[a]
 */
?>
		</div>
		<footer id="admin-footer" class="clear">
			<div class="copyright"><?php RSCopyright(); ?></div>
			<div class="version"><?php RSVersion(); ?></div>
		</footer>
		<?php adminFooterScripts(); ?>
	</body>
</html>
<?php
// End output buffering
ob_end_flush();