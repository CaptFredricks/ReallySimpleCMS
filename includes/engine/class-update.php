<?php
/**
 * Core class used to implement the Update object.
 * This file handles system updates.
 * @since 1.4.0-beta_snap-03
 *
 * @package ReallySimpleCMS
 * @subpackage Engine
 *
 * ## OBJECT VAR ##
 * - $rs_update
 *
 * ## METHODS [7] ##
 * - public __construct()
 * { UPDATERS [2] }
 * - public updateModule(string $name): string
 * - public updateAdminTheme(string $name): string
 * { MISCELLANEOUS [4] }
 * - public isUpdateAvailable(string $project, string $version): bool
 * - public getCurrentVersion(string $project): string
 * - private zipDir(string $source, string $destination): void
 * - private dirToZip(string $dir, object &$zip, int $exclude_length): void
 */
namespace Engine;

class Update {
	/**
	 * Class constructor.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 */
	public function __construct() {
		global $rs_api_fetch;
		
		$rs_api_fetch = new \Engine\ApiFetch;
	}
	
	/*------------------------------------*\
		UPDATERS
	\*------------------------------------*/
	
	/**
	 * Update a module.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 * @param string $name -- The module's name.
	 * @return string
	 */
	public function updateModule(string $name): string {
		global $rs_modules, $rs_doing_update;
		
		// Put the system in update mode
		$rs_doing_update = true;
		
		if(!moduleExists($name)) {
			return notice('Module does not exist.', -1, false, true);
			exit;
		}
		
		// Try to fetch the update package
		domTagPr('p', array(
			'content' => 'Fetching latest update package...'
		));
		
		$api_fetch = new \Engine\ApiFetch($name);
		$download = pathinfo($api_fetch->getDownload());
		
		// Create temp dirs
		domTagPr('p', array(
			'content' => 'Creating temporary directories...'
		));
		
		$modules_file_path = slash(PATH . MODULES);
		$temp_file_path = $modules_file_path . slash('update-temp');
		$filename = strtok($download['basename'], '?');
		$zip_file = $temp_file_path . $filename;
		
		if(!file_exists($temp_file_path)) mkdir($temp_file_path);
		
		// Copy over the zip file and unzip
		domTagPr('p', array(
			'content' => 'Unzipping package...'
		));
		
		$file = file_put_contents($zip_file, fopen($api_fetch->getDownload(), 'r'), LOCK_EX);
		
		if($file !== false) {
			$zip = new \ZipArchive;
			$res = $zip->open($zip_file);
			
			if($res === true) {
				$zip->extractTo($temp_file_path);
				$zip->close();
			} else {
				var_dump($res);
				exit;
			}
		}
		
		// Install the new package
		domTagPr('p', array(
			'content' => 'Installing package...'
		));
		
		if(file_exists($temp_file_path . slash($name)))
			$temp_file_path_mod = $temp_file_path . slash($name);
		elseif(file_exists($temp_file_path . slash($rs_modules[$name]['label'] . '-master')))
			$temp_file_path_mod = $temp_file_path . slash($rs_modules[$name]['label'] . '-master');
		
		if(isset($temp_file_path_mod)) {
			$mod_file_path = $modules_file_path . slash($name);
			
			if(file_exists($mod_file_path))
				rename($mod_file_path, $modules_file_path . slash($name . '-old_temp'));
			
			rename($temp_file_path_mod, $mod_file_path);
		} else {
			removeDir($temp_file_path);
			
			return notice('New version could not be installed.', -1, false, true);
			exit;
		}
		
		// Archive the old files
		domTagPr('p', array(
			'content' => 'Archiving old files...'
		));
		
		$backups_file_path = $modules_file_path . slash('backups');
		
		if(!file_exists($backups_file_path)) mkdir($backups_file_path);
		if(file_exists($backups_file_path . $name . '.zip')) unlink($backups_file_path . $name . '.zip');
		
		// Create zip archive
		$this->zipDir($modules_file_path . slash($name . '-old_temp'), $backups_file_path . $name . '.zip');
		
		// Clean up temp files
		domTagPr('p', array(
			'content' => 'Cleaning up...'
		));
		
		removeDir($modules_file_path . slash($name . '-old_temp'));
		removeDir($temp_file_path);
		
		// Take the system out of update mode
		$rs_doing_update = false;
		
		return notice('Module successfully updated.', 1, false, true);
	}
	
	/**
	 * Update an admin theme.
	 * @since 1.4.0-beta_snap-04
	 *
	 * @access public
	 * @param string $name -- The admin theme's name.
	 * @return string
	 */
	public function updateAdminTheme(string $name): string {
		global $rs_admin_themes, $rs_doing_update;
		
		// Put the system in update mode
		$rs_doing_update = true;
		
		if(!adminThemeExists($name)) {
			return notice('Admin theme does not exist.', -1, false, true);
			exit;
		}
		
		// Try to fetch the update package
		domTagPr('p', array(
			'content' => 'Fetching latest update package...'
		));
		
		$api_fetch = new \Engine\ApiFetch($name);
		$download = pathinfo($api_fetch->getDownload());
		
		// Create temp dirs
		domTagPr('p', array(
			'content' => 'Creating temporary directories...'
		));
		
		$admin_themes_file_path = slash(PATH . ADMIN_THEMES);
		$temp_file_path = $admin_themes_file_path . slash('update-temp');
		$filename = strtok($download['basename'], '?');
		$zip_file = $temp_file_path . $filename;
		
		if(!file_exists($temp_file_path)) mkdir($temp_file_path);
		
		// Copy over the zip file and unzip
		domTagPr('p', array(
			'content' => 'Unzipping package...'
		));
		
		$file = file_put_contents($zip_file, fopen($api_fetch->getDownload(), 'r'), LOCK_EX);
		
		if($file !== false) {
			$zip = new \ZipArchive;
			$res = $zip->open($zip_file);
			
			if($res === true) {
				$zip->extractTo($temp_file_path);
				$zip->close();
			} else {
				var_dump($res);
				exit;
			}
		}
		
		// Install the new package
		domTagPr('p', array(
			'content' => 'Installing package...'
		));
		
		if(file_exists($temp_file_path . slash($name)))
			$temp_file_path_adtheme = $temp_file_path . slash($name);
		elseif(file_exists($temp_file_path . slash($rs_admin_themes[$name]['label'] . '-master')))
			$temp_file_path_adtheme = $temp_file_path . slash($rs_admin_themes[$name]['label'] . '-master');
		
		if(isset($temp_file_path_adtheme)) {
			$adtheme_file_path = $admin_themes_file_path . slash($name);
			
			if(file_exists($adtheme_file_path))
				rename($adtheme_file_path, $admin_themes_file_path . slash($name . '-old_temp'));
			
			rename($temp_file_path_adtheme, $adtheme_file_path);
		} else {
			removeDir($temp_file_path);
			
			return notice('New version could not be installed.', -1, false, true);
			exit;
		}
		
		// Archive the old files
		domTagPr('p', array(
			'content' => 'Archiving old files...'
		));
		
		$backups_file_path = $admin_themes_file_path . slash('backups');
		
		if(!file_exists($backups_file_path)) mkdir($backups_file_path);
		if(file_exists($backups_file_path . $name . '.zip')) unlink($backups_file_path . $name . '.zip');
		
		// Create zip archive
		$this->zipDir($admin_themes_file_path . slash($name . '-old_temp'), $backups_file_path . $name . '.zip');
		
		// Clean up temp files
		domTagPr('p', array(
			'content' => 'Cleaning up...'
		));
		
		removeDir($admin_themes_file_path . slash($name . '-old_temp'));
		removeDir($temp_file_path);
		
		// Take the system out of update mode
		$rs_doing_update = false;
		
		return notice('Admin theme successfully updated.', 1, false, true);
	}
	
	/*------------------------------------*\
		MISCELLANEOUS
	\*------------------------------------*/
	
	/**
	 * Check if an update is available.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 * @param string $project -- The project to check.
	 * @param string $version -- The project's version.
	 * @return bool
	 */
	public function isUpdateAvailable(string $project, string $version): bool {
		$api_fetch = new \Engine\ApiFetch($project);
		
		return version_compare($version, $this->getCurrentVersion($project), '<');
	}
	
	/**
	 * Fetch the current software version.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access public
	 * @param string $project -- The project to check.
	 * @return string
	 */
	public function getCurrentVersion(string $project): string {
		$api_fetch = new \Engine\ApiFetch($project);
		
		return $api_fetch->getVersion();
	}
	
	/**
	 * Zip a directory, e.g., zipDir('/path/to/sourceDir', '/path/to/out.zip').
	 * @since 1.4.0-beta_snap-03
	 *
	 * @access private
	 * @param string $source -- Source directory path.
	 * @param string $destination -- Zip destination path.
	 */
	private function zipDir(string $source, string $destination): void {
		$source_path = pathInfo($source);
		$parent_path = $source_path['dirname'];
		$dir_name = $source_path['basename'];

		$zip = new \ZipArchive();
		$zip->open($destination, \ZIPARCHIVE::CREATE);
		$zip->addEmptyDir($dir_name);
		$this->dirToZip($source, $zip, strlen("$parent_path/"));
		$zip->close();
	}
	
	/**
	 * Add files and sub-directories to a zip file.
	 * @since 1.4.0-beta_snap-03
	 *
	 * @param string $dir -- The directory.
	 * @param object $zip -- The zip archive.
	 * @param int $exclude_length -- Position of text to be excluded from the file path.
	 */
	private function dirToZip(string $dir, object &$zip, int $exclude_length): void {
		if(is_dir($dir)) {
			$handle = opendir($dir);
			
			while($file = readdir($handle) !== false) {
				if($file != '.' && $file != '..') {
					$file_path = "$dir/$f";
					
					// Remove prefix from file path before adding to zip
					$local_path = substr($file_path, $exclude_length);
					
					if(is_file($file_path)) {
						$zip->addFile($file_path, $local_path);
					} elseif(is_dir($file_path)) {
						// Add sub-directories
						$zip->addEmptyDir($local_path);

						$this->dirToZip($file_path, $zip, $exclude_length);
					}
				}
			}
			
			closedir($handle);
		}
	}
}