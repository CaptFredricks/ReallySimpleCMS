<?php
/**
 * Admin class used to implement the Update object.
 * @since 1.4.0-beta_snap-04
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_update
 *
 * ## VARIABLES [4] ##
 * - private string $core
 * - private string $type
 * - private string $name
 * - private string $action
 *
 * ## METHODS [5] ##
 * - public __construct(string $type, string $name, string $action)
 * { LISTS, FORMS, & ACTIONS [4] }
 * - public listCoreUpdates(): void
 * - public listModuleUpdates(): void
 * - public listThemeUpdates(): void
 * - public listAdminThemeUpdates(): void
 */
namespace Admin;

class Update {
	/**
	 * The core software.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var string
	 */
	private $core = 'rscms';
	
	/**
	 * The currently queried update's type.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var string
	 */
	private $type;
	
	/**
	 * The currently queried update's name.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The current action.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * Class constructor.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @param string $type -- The update's type.
	 * @param string $name -- The update's name.
	 * @param string $action -- The current action.
	 */
	public function __construct(string $type, string $name, string $action) {
		$this->type = $type;
		$this->name = $name;
		$this->action = $action;
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * List all available core updates.
	 * @since 1.4.0-beta_snap-04
	 */
	public function listCoreUpdates(): void {
		global $rs_update;
		
		domTagPr('h2', array(
			'content' => 'Core Software'
		));
		
		domTagPr('p', array(
			'content' => domTag('strong', array(
				'content' => 'Current version: ' . RS_VERSION
			))
		));
		
		if($rs_update->isUpdateAvailable($this->core, RS_VERSION)) {
			// Update available
			domTagPr('p', array(
				'content' => 'An update is available for ' . RS_ENGINE . '. You\'re running ' . domTag('strong', array(
					'content' => RS_VERSION
				)) . ', and the latest version is ' . domTag('strong', array(
					'content' => $rs_update->getCurrentVersion($this->core)
				)) . '.'
			));
			
			domTagPr('form', array(
				'method' => 'post',
				'content' => domTag('input', array(
					'type' => 'hidden',
					'name' => 'do_update',
					'value' => 1 // check should come from here
				)) . domTag('input', array(
					'type' => 'submit',
					'class' => 'submit-input button',
					'name' => 'submit',
					'value' => 'Update Core'
				))
			));
		} else {
			// Everything is up to date
			domTagPr('p', array(
				'content' => RS_ENGINE . ' is up to date.'
			));
		}
	}
	
	/**
	 * List all modules with available updates.
	 * @since 1.4.0-beta_snap-04
	 */
	public function listModuleUpdates(): void {
		global $rs_update, $rs_modules;
		
		domTagPr('h2', array(
			'content' => 'Modules'
		));
		?>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$header_cols = array(
					'bulk-select' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'name' => 'Name',
					'author' => 'Author',
					'current-version' => 'Current Version',
					'available-version' => 'Available Version'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				foreach($rs_modules as $module) {
					$needs_update = false;
					
					if($rs_update->isUpdateAvailable($module['name'], $module['version'])) {
						$needs_update = true;
						
						// Action links
						$actions = array(
							// Update
							actionLink('update', array(
								'caption' => 'Update',
								'type' => 'module',
								'name' => $module['name']
							))
							// Uninstall
						);
						
						// Filter out any empty actions
						$actions = array_filter($actions);
						
						if($needs_update === true) {
							echo tableRow(
								// Bulk select
								tdCell(domTag('input', array(
									'type' => 'checkbox',
									'class' => 'checkbox',
									'value' => ''
								)), 'bulk-select'),
								tdCell(domTag('strong', array(
									'content' => $module['label']
								)) . domTag('br') . domTag('div', array(
									'class' => 'actions',
									'content' => implode(' &bull; ', $actions)
								)), 'name'),
								tdCell(domTag('a', array(
									'href' => $module['author']['url'],
									'content' => $module['author']['name'],
									'target' => '_blank',
									'rel' => 'noreferrer noopener'
								)), 'author'),
								tdCell($module['version'], 'current-version'),
								tdCell($rs_update->getCurrentVersion($module['name']), 'available-version')
							);
						}
					}
				}
				
				if($needs_update === false) {
					// Everything is up to date
					echo tableRow(
						tdCell('All modules are up to date.', '', 5)
					);
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		if($this->action === 'update' && $this->type === 'module') {
			$rs_update->updateModule($this->name);
			
			redirect('update.php');
		}
	}
	
	/**
	 * List all themes with available updates.
	 * @since 1.4.0-beta_snap-04
	 */
	public function listThemeUpdates(): void {
		global $rs_update, $rs_themes;
		
		domTagPr('h2', array(
			'content' => 'Themes'
		));
	}
	
	/**
	 * List all admin themes with available updates.
	 * @since 1.4.0-beta_snap-04
	 */
	public function listAdminThemeUpdates(): void {
		global $rs_update, $rs_admin_themes;
		
		domTagPr('h2', array(
			'content' => 'Admin Themes'
		));
		?>
		<table class="data-table has-bulk-select">
			<thead>
				<?php
				$header_cols = array(
					'bulk-select' => domTag('input', array(
						'type' => 'checkbox',
						'class' => 'checkbox bulk-selector'
					)),
					'name' => 'Name',
					'author' => 'Author',
					'current-version' => 'Current Version',
					'available-version' => 'Available Version'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				foreach($rs_admin_themes as $admin_theme) {
					$needs_update = false;
					
					if($rs_update->isUpdateAvailable($admin_theme['name'], $admin_theme['version'])) {
						$needs_update = true;
						
						// Action links
						$actions = array(
							// Update
							actionLink('update', array(
								'caption' => 'Update',
								'type' => 'admin-theme',
								'name' => $admin_theme['name']
							))
							// Uninstall
						);
						
						// Filter out any empty actions
						$actions = array_filter($actions);
						
						if($needs_update === true) {
							echo tableRow(
								// Bulk select
								tdCell(domTag('input', array(
									'type' => 'checkbox',
									'class' => 'checkbox',
									'value' => ''
								)), 'bulk-select'),
								tdCell(domTag('strong', array(
									'content' => $admin_theme['label']
								)) . domTag('br') . domTag('div', array(
									'class' => 'actions',
									'content' => implode(' &bull; ', $actions)
								)), 'name'),
								tdCell(domTag('a', array(
									'href' => $admin_theme['author']['url'],
									'content' => $admin_theme['author']['name'],
									'target' => '_blank',
									'rel' => 'noreferrer noopener'
								)), 'author'),
								tdCell($admin_theme['version'], 'current-version'),
								tdCell($rs_update->getCurrentVersion($admin_theme['name']), 'available-version')
							);
						}
					}
				}
				
				if($needs_update === false) {
					// Everything is up to date
					echo tableRow(
						tdCell('All admin themes are up to date.', '', 5)
					);
				}
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		if($this->action === 'update' && $this->type === 'admin-theme') {
			$rs_update->updateAdminTheme($this->name);
			
			redirect('update.php');
		}
	}
}