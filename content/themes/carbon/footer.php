			<div id="scroll-top">
				<i class="fa-solid fa-chevron-up"></i>
			</div>
		</main>
		<footer class="footer">
			<div class="wrapper">
				<div class="row">
					<div class="col-4">
						<?php getMenu('footer-menu'); ?>
					</div>
					<div class="col-4">
						<?php getWidget('business-info', true); ?>
					</div>
					<div class="col-4">
						<?php getRecentPosts(3, getTaxonomyId('category'), true); ?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<?php getWidget('social-media'); ?>
						<?php getWidget('copyright'); ?>
					</div>
				</div>
			</div>
		</footer>
		<?php if($session) adminBar(); ?>
		<?php footerScripts('', array(), array(array('script'))); ?>
	</body>
</html>