<?php
/**
 * Run the database installation.
 * @since 1.2.6-beta
 *
 * @package ReallySimpleCMS
 */

require_once dirname(__DIR__) . '/includes/constants.php';
require_once RS_CRIT_FUNC;

checkPHPSetup();

if(isset($_POST['submit_ajax']) && $_POST['submit_ajax']) {
	requireFiles(array(RS_CONFIG, RS_DEBUG_FUNC, RS_GLOBAL_FUNC));
	
	$rs_query = new \Engine\Query;
	checkDBStatus();
	
	$rs_install = new \Engine\Install;
	
	// Register required modules
	runModulesRegister(true);
	
	$result = $rs_install->runInstall($_POST);
	
	echo implode(';', $result);
}