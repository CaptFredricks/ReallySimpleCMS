<?php
/**
 * Admin class used to implement the Module object.
 * Modules can bee seen as extensions of core functionality. They are counted as external libraries, and their code exists semi-independent of core code, utilizing it to run their own code.
 * @since 1.4.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 * @subpackage Admin
 *
 * ## OBJECT VAR ##
 * - $rs_ad_module
 *
 * ## VARIABLES [5] ##
 * - private string $name
 * - private array $mod_data
 * - private array $admin_page
 * - private string $action
 * - private array $paged
 *
 * ## METHODS [14] ##
 * - public __construct(string $name, string $action, array $mod_data)
 * { LISTS, FORMS, & ACTIONS [5] }
 * - public listRecords(): void
 * - public installModule(): void
 * - public activateModule(): void
 * - public deactivateModule(): void
 * - public updateModule(): void
 * { VALIDATION [1] }
 * - private validateSubmission(array $data): string
 * { MISCELLANEOUS [7] }
 * - public pageHeading(): void
 * - private exitNotice(string $exit_status, int $status_code): string
 * - private bulkActions(): void
 * - private isActive(string $name): bool
 * - private getResults(string $status, ?string $search): array
 * - private getEntryCount(string $status, ?string $search): int
 * - private getActionLinks(array $module): string
 */
namespace Admin;

class Module {
	/**
	 * The module's name.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @var string
	 */
	private $name;
	
	/**
	 * The module's data.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @var array
	 */
	private $mod_data = array();
	
	/**
	 * The admin page's data.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @var array
	 */
	private $admin_page = array();
	
	/**
	 * The current action.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @var string
	 */
	private $action;
	
	/**
	 * The pagination.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @var array
	 */
	private $paged = array();
	
	/**
	 * Class constructor.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 * @param string $name -- The module's name.
	 * @param string $action -- The current action.
	 * @param array $mod_data (optional) -- The module data.
	 */
	public function __construct(string $name, string $action, array $mod_data = array()) {
		global $rs_admin_pages;
		
		$this->name = $name;
		$this->action = $action;
		$this->mod_data = $mod_data;
		$this->admin_page = $rs_admin_pages[basename($_SERVER['PHP_SELF'], '.php')];
	}
	
	/*------------------------------------*\
		LISTS, FORMS, & ACTIONS
	\*------------------------------------*/
	
	/**
	 * Construct a list of all modules.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 */
	public function listRecords(): void {
		global $rs_update;
		
		// Query vars
		$status = $_GET['status'] ?? 'all';
		$search = $_GET['search'] ?? null;
		$this->paged = paginate((int)($_GET['paged'] ?? 1));
		
		$this->pageHeading();
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
					'version' => 'Version',
					'description' => 'Description'
				);
				
				echo tableHeaderRow($header_cols);
				?>
			</thead>
			<tbody>
				<?php
				$modules = $this->getResults($status, $search);
				
				foreach($modules as $module) {
					echo tableRow(
						// Bulk select
						tdCell(domTag('input', array(
							'type' => 'checkbox',
							'class' => 'checkbox',
							'value' => $module['name']
						)), 'bulk-select'),
						// Name
						tdCell(domTag('strong', array(
							'content' => $module['label']
						)) . (!$this->isActive($module['name']) ? ' &mdash; ' . domTag('em', array(
								'content' => 'inactive'
							)) : '') . domTag('br') . domTag('div', array(
							'class' => 'actions',
							'content' => !$module['is_required'] ? $this->getActionLinks($module) : domTag('em', array(
								'content' => 'required modules cannot be modified'
							))
						)), 'name'),
						// Author
						tdCell(domTag('a', array(
							'href' => $module['author']['url'],
							'content' => $module['author']['name'],
							'target' => '_blank',
							'rel' => 'noreferrer noopener'
						)), 'author'),
						// Version
						tdCell($module['version'] . ($rs_update->isUpdateAvailable($module['name'], $module['version']) ?
							' (' . domTag('a', array(
								'href' => ADMIN_URI . getQueryString(array(
									'name' => $module['name'],
									'action' => 'update',
								)),
								'content' => 'update'
							)) . ')' : ''), 'version'),
						// Description
						tdCell(!empty($module['description']) ? $module['description'] : '&mdash;', 'description'),
					);
				}
				
				if(empty($modules))
					echo tableRow(tdCell($this->admin_page['labels']['no_items'], '', count($header_cols)));
				?>
			</tbody>
			<tfoot>
				<?php echo tableHeaderRow($header_cols); ?>
			</tfoot>
		</table>
		<?php
		// Bulk actions
		if(!empty($modules)) $this->bulkActions();
		
		// Set up page navigation
		echo pagerNav($this->paged['current'], $this->paged['count']);
		
        includeFile(PATH . MODALS . '/modal-delete.php');
	}
	
	/**
	 * Install a module.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 */
	public function installModule(): void {
		global $rs_modules;
		
		$this->pageHeading();
		
		$api_fetch = new \Engine\ApiFetch('modules');
		$modules = json_decode($api_fetch->getModules(), true);
		$modules = array_merge([], ...$modules);
		
		$list = array(
			domTag('option', array(
				'value' => '',
				'content' => 'Select one...'
			))
		);
		
		foreach($modules as $key => $val) {
			if(array_key_exists($key, $rs_modules)) continue;
			
			$list[] = domTag('option', array(
				'value' => $key,
				'content' => $val
			));
		}
		?>
		<div class="data-form-wrap clear">
			<form class="data-form" action="" method="post" autocomplete="off" enctype="multipart/form-data">
				<table class="form-table">
					<?php
					// Upload
					
					// Available modules
					echo formRow('Available Modules', array(
						'tag' => 'select',
						'id' => 'install-field',
						'class' => 'select-input',
						'name' => 'install',
						'content' => implode('', $list)
					));
					
					// Separator
					echo formRow('', array(
						'tag' => 'hr',
						'class' => 'separator'
					));
					
					// Submit button
					echo formRow('', array(
						'tag' => 'input',
						'type' => 'submit',
						'class' => 'submit-input button',
						'name' => 'submit',
						'value' => $this->admin_page['labels']['create_button']
					));
					?>
				</table>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Activate a registered module.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 */
	public function activateModule(): void {
		global $rs_query, $rs_modules;
		
		$active_modules = array();
		$db_active_modules = getSetting('active_modules');
		
		if(!empty($this->name) && moduleExists($this->name) && !isActiveModule($this->name)) {
			foreach($rs_modules as $module) {
				if($module['is_required'] === true || $module['name'] === $this->name)
					$active_modules[] = $module['name'];
			}
			
			$active_modules = serialize($active_modules);
			
			if($active_modules !== $db_active_modules) {
				$rs_query->update(getTable('s'), array(
					'value' => $active_modules
				), array(
					'name' => 'active_modules'
				));
			}
			
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'activate_success'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'activate_failure'
		)));
	}
	
	/**
	 * Deactivate a registered module.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 */
	public function deactivateModule(): void {
		global $rs_query, $rs_modules;
		
		$active_modules = array();
		$db_active_modules = getSetting('active_modules');
		
		if(!empty($this->name) && moduleExists($this->name) && isActiveModule($this->name)) {
			foreach($rs_modules as $module) {
				if($module['name'] === $this->name)
					continue;
				elseif($module['is_required'] === true)
					$active_modules[] = $module['name'];
			}
			
			$active_modules = serialize($active_modules);
			
			if($active_modules !== $db_active_modules) {
				$rs_query->update(getTable('s'), array(
					'value' => $active_modules
				), array(
					'name' => 'active_modules'
				));
			}
			
			redirect(ADMIN_URI . getQueryString(array(
				'exit_status' => 'deactivate_success'
			)));
		}
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'deactivate_failure'
		)));
	}
	
	/**
	 * Update a module.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 */
	public function updateModule(): void {
		global $rs_update;
		?>
		<div class="data-form-wrap clear">
			<?php
			domTagPr('p', array(
				'content' => 'Updating ' . domTag('strong', array(
					'content' => $this->mod_data['label']
				)) . ' from ' . domTag('strong', array(
					'content' => $this->mod_data['version']
				)) . ' to ' . domTag('strong', array(
					'content' => $rs_update->getCurrentVersion($this->mod_data['name'])
				)) . ':'
			));
			
			echo $rs_update->updateModule($this->mod_data['name']);
			?>
		</div>
		<?php
	}
	
	/*------------------------------------*\
		VALIDATION
	\*------------------------------------*/
	
	/**
	 * Validate the form data.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $data -- The submission data.
	 * @return string
	 */
	private function validateSubmission(array $data): string {
		global $rs_query, $rs_modules;
		
		if(empty($data['install'])) {
			return exitNotice('You must select a module to install!', -1);
			exit;
		}
		
		if(moduleExists($data['install'])) {
			return exitNotice('Module is already installed.', -1);
			exit;
		}
		
		$api_fetch = new \Engine\ApiFetch($data['install']);
		$download = pathinfo($api_fetch->getDownload());
		$modules_file_path = slash(PATH . MODULES);
		$filename = strtok($download['basename'], '?');
		$zip_file = $modules_file_path . $filename;
		$file = file_put_contents($zip_file, fopen($api_fetch->getDownload(), 'r'), LOCK_EX);
		
		if($file !== false) {
			$zip = new \ZipArchive;
			$res = $zip->open($zip_file);
			
			if($res === true) {
				$zip->extractTo($modules_file_path);
				$zip->close();
			} else {
				var_dump($res);
				exit;
			}
		}
		
		// Remove zip file
		unlink($zip_file);
		
		redirect(ADMIN_URI . getQueryString(array(
			'exit_status' => 'install_success'
		)));
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Construct the page heading.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 */
	public function pageHeading(): void {
		$labels = $this->admin_page['labels'];
		
		switch($this->action) {
			case 'install':
				$title = $labels['create_item'];
				$message = isset($_POST['submit']) ? $this->validateSubmission($_POST) : '';
				break;
			default:
				$title = $labels['name'];
				$status = $_GET['status'] ?? 'all';
				$search = $_GET['search'] ?? null;
		}
		?>
		<div class="heading-wrap">
			<?php
			// Page title
			domTagPr('h1', array(
				'content' => $title
			));
			
			if(!empty($this->action)) {
				// Status messages
				echo $message;
				
				// Exit notices
				if(isset($_GET['exit_status']))
					echo $this->exitNotice($_GET['exit_status']);
			} else {
				// Install button
				//if(userHasPrivilege('can_install_modules')) {
					echo actionLink('install', array(
						'classes' => 'button',
						'caption' => $labels['create_button']
					));
				//}
				
				// Search
				recordSearch(array(
					'status' => $status
				));
				
				//Info
				adminInfo();
				
				domTagPr('hr');
				
				// Exit notices
				if(isset($_GET['exit_status'])) {
					$exit = $_GET['exit_status'];
					
					if($exit === 'activate_failure' || $exit === 'del_failure')
						$status_code = -1;
					
					if(isset($status_code))
						echo $this->exitNotice($exit, $status_code);
					else
						echo $this->exitNotice($exit);
				}
				?>
				<ul class="status-nav">
					<?php
					$keys = array('all', 'active', 'inactive', 'required', 'update');
					$count = array();
					
					foreach($keys as $key)
						$count[$key] = $this->getEntryCount($key, $search);
					
					// Statuses
					foreach($count as $key => $value) {
						domTagPr('li', array(
							'content' => domTag('a', array(
								'href' => ADMIN_URI . ($key === 'all' ? '' : '?status=' . $key),
								'content' => ucfirst($key) . ' ' . domTag('span', array(
									'class' => 'count',
									'content' => '(' . $value . ')'
								))
							))
						));
						
						if($key !== array_key_last($count)) echo ' &bull; ';
					}
					?>
				</ul>
				<?php
				// Record count
				domTagPr('div', array(
					'class' => 'entry-count status',
					'content' => $count[$status] . ' ' . ($count[$status] === 1 ? 'entry' : 'entries')
				));
				
				$this->paged['count'] = ceil($count[$status] / $this->paged['per_page']);
			}
			?>
		</div>
		<?php
	}
	
	/**
	 * Generate an exit notice.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @param string $exit_status -- The exit status.
	 * @param int $status_code (optional) -- The type of notice to display.
	 * @return string
	 */
	private function exitNotice(string $exit_status, int $status_code = 1): string {
		return exitNotice(match($exit_status) {
			'install_success' => 'The module was successfully installed.',
			'activate_success' => 'The module was successfully activated.',
			'activate_failure' => 'The module could not be activated.',
			'deactivate_success' => 'The module was successfully deactivated.',
			'deactivate_failure' => 'The module could not be deactivated.',
			'del_success' => 'The module was successfully deleted.',
			'del_failure' => 'The module could not be deleted.',
			default => 'The action was completed successfully.'
		}, $status_code);
	}
	
	/**
	 * Construct bulk actions.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 */
	private function bulkActions(): void {
		global $rs_modules;
		
		$labels = $this->admin_page['labels'];
		?>
		<div class="bulk-actions">
			<?php
			#if(userHasPrivilege('can_edit_modules')) {
				$statuses = array('active', 'inactive');
				$list = array();
				
				foreach($statuses as $status) {
					$list[] = domTag('option', array(
						'value' => $status,
						'content' => ucfirst($status)
					));
				}
				
				domTagPr('select', array(
					'class' => 'actions',
					'content' => implode('', $list)
				));
				
				// Update status
				button(array(
					'class' => 'bulk-update',
					'title' => 'Bulk status update',
					'label' => $labels['bulk_update']
				));
			#}
			
			#if(userHasPrivilege('can_uninstall_modules')) {
				// Uninstall
				button(array(
					'class' => 'bulk-delete',
					'title' => 'Bulk ' . $this->admin_page['actions']['uninstall'],
					'label' => $labels['bulk_delete']
				));
			#}
			?>
		</div>
		<?php
	}
	
	/**
	 * Check whether a specified module is active.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @param string $name -- The module's name.
	 * @return bool
	 */
	private function isActive(string $name): bool {
		$active_modules = getSetting('active_modules');
		
		if(!empty($active_modules)) {
			$active_modules = unserialize($active_modules);
			
			return in_array($name, $active_modules, true);
		}
		
		return false;
	}
	
	/**
	 * Fetch all modules based on a specific status.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @param string $status -- The module's status.
	 * @param null|string $search -- The search query.
	 * @return array
	 */
	private function getResults(string $status, ?string $search): array {
		global $rs_update, $rs_modules;
		
		$modules = array();
		
		if(!is_null($search)) {
			// Search results
			switch($status) {
				case 'active':
					foreach($rs_modules as $module) {
						if($this->isActive($module['name']) && str_contains(strtolower($module['label']), $search))
							$modules[] = $module;
					}
					break;
				case 'inactive':
					foreach($rs_modules as $module) {
						if(!$this->isActive($module['name']) && str_contains(strtolower($module['label']), $search))
							$modules[] = $module;
					}
					break;
				case 'required':
					foreach($rs_modules as $module) {
						if($module['is_required'] === true && str_contains(strtolower($module['label']), $search))
							$modules[] = $module;
					}
					break;
				case 'update':
					break;
				default:
					foreach($rs_modules as $module) {
						if($rs_update->isUpdateAvailable($module['name'], $module['version']) &&
							str_contains(strtolower($module['label']), $search))
						{
							$modules[] = $module;
						}
					}
			}
		} else {
			// All results
			switch($status) {
				case 'active':
					foreach($rs_modules as $module) {
						if($this->isActive($module['name']))
							$modules[] = $module;
					}
					break;
				case 'inactive':
					foreach($rs_modules as $module) {
						if(!$this->isActive($module['name']))
							$modules[] = $module;
					}
					break;
				case 'required':
					foreach($rs_modules as $module) {
						if($module['is_required'] === true)
							$modules[] = $module;
					}
					break;
				case 'update':
					foreach($rs_modules as $module) {
						if($rs_update->isUpdateAvailable($module['name'], $module['version']))
							$modules[] = $module;
					}
					break;
				default:
					$modules = $rs_modules;
			}
		}
		
		return $modules;
	}
	
	/**
	 * Fetch the entry count based on a specific status.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @param string $status -- The module's status.
	 * @param null|string $search -- The search query.
	 * @return int
	 */
	private function getEntryCount(string $status, ?string $search): int {
		return count($this->getResults($status, $search));
	}
	
	/**
	 * Fetch all associated action links.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access private
	 * @param array $module -- The module's data.
	 * @return string
	 */
	private function getActionLinks(array $module): string {
		$actions = $this->admin_page['actions'];
		$action_list = array();
		
		foreach($actions as $key => $value) {
			if($value === 'install') continue;
			
			$privileged = match($value) {
				#'activate', 'deactivate' => userHasPrivilege('can_edit_modules'),
				#'uninstall' => userHasPrivilege('can_uninstall_modules'),
				default => null
			};
			
			$action_list[] = array(
				'privileged' => $privileged,
				'link' => $value,
				'caption' => ucfirst($value)
			);
		}
		
		list($uninstall, $activate, $deactivate) = $action_list;
		
		$action_links = array(
			// Activate/deactivate
			$this->isActive($module['name']) ? actionLink($deactivate['link'], array(
				'caption' => $deactivate['caption'],
				'name' => $module['name']
			)) : actionLink($activate['link'], array(
				'caption' => $activate['caption'],
				'name' => $module['name']
			)),
			// Uninstall
			!$this->isActive($module['name']) ? actionLink($uninstall['link'], array(
				'classes' => 'modal-launch delete-item',
				'data_item' => 'module',
				'caption' => $uninstall['caption'],
				'name' => $module['name']
			)) : null
		);
		
		// Filter out any empty actions
		$action_links = array_filter($action_links);
		
		return implode(' &bull; ', $action_links);
	}
}