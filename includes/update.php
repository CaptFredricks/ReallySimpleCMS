<?php
/**
 * Update the CMS.
 * @since 1.1.0-beta_snap-01
 *
 * @package ReallySimpleCMS
 */

require_once PATH . INC . '/update-db.php';
require_once PATH . INC . '/backward-compat.php';

$rs_api_fetch = new \Engine\ApiFetch;

/* var_dump($rs_api_fetch->getVersion());
var_dump(CMS_VERSION);

var_dump(version_compare(CMS_VERSION, $rs_api_fetch->getVersion(), '<')); */

# if do_update then UPDATE