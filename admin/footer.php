<?php
/**
 * Admin dashboard footer.
 * @since 1.0.2-alpha
 *
 * @package ReallySimpleCMS
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
ob_end_flush();