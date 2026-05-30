<?php
/**
 * Run the database installation script.
 * @since 1.3.0-alpha
 *
 * @package ReallySimpleCMS
 */

require_once dirname(__DIR__) . '/includes/constants.php';
require_once RS_CRIT_FUNC;

checkPHPSetup();

if(!file_exists(RS_CONFIG)) redirect(SETUP . '/rsdb-config.php');

requireFiles(array(RS_CONFIG, RS_DEBUG_FUNC, RS_GLOBAL_FUNC));

$rs_query = new \Engine\Query;
checkDBStatus();

requireFile(RS_SCHEMA);

$schema = dbSchema();
$tables = $rs_query->showTables();

if(!empty($tables)) {
	// Create any missing tables
	foreach($schema as $key => $value)
		if(!$rs_query->tableExists($key)) $rs_query->createTable($key, $value);
	
	exit(RS_ENGINE . ' is already installed!');
}

$rs_install = new \Engine\Install;
$debug = false;

if(isDebugMode()) $debug = true;

// Register required modules
runModulesRegister(true);

$step = (int)($_GET['step'] ?? 1);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
		domTagPr('title', array(
			'content' => RS_ENGINE . ' Database Installation'
		));
		
		domTagPr('meta', array(
			'charset' => 'UTF-8'
		));
		
		domTagPr('meta', array(
			'name' => 'viewport',
			'content' => 'width=device-width, initial-scale=1.0'
		));
		
		domTagPr('meta', array(
			'name' => 'robots',
			'content' => 'noindex, nofollow'
		));
		
		putStylesheet('global' . ($debug ? '' : '.min') . '.css');
		putStylesheet('button' . ($debug ? '' : '.min') . '.css');
		putStylesheet('setup' . ($debug ? '' : '.min') . '.css');
		putStylesheet('font-awesome.min.css', ICONS_VERSION);
		putStylesheet('font-awesome-rules.min.css');
		?>
	</head>
	<body class="rscms-install">
		<div class="wrapper">
			<?php
			domTagPr('h1', array(
				'content' => RS_ENGINE
			));
			?>
			<main class="content" role="main">
				<?php
				switch($step) {
					case 1:
						$rs_install->installForm();
						break;
					case 2:
						list($error, $message) = $rs_install->runInstall($_POST);
						
						if($error)
							$rs_install->installForm($message);
						else
							echo $message;
						break;
				}
				?>
			</main>
			<?php
			domTagPr('p', array(
				'class' => 'powered-by',
				'content' => 'Powered by ' . RS_DEVELOPER
			));
			?>
		</div>
		<?php
		putScript('jquery.min.js', JQUERY_VERSION);
		putScript('setup' . ($debug ? '' : '.min') . '.js');
		?>
	</body>
</html>