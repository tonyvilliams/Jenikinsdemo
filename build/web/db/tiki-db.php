<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: tiki-db.php 46084 2013-05-29 14:59:41Z jonnybradley $

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
	header('location: index.php');
	exit;
}

require_once('lib/init/initlib.php');

// Define lang and load translation functions
if (!empty($_REQUEST['lang'])) {
	$language = $prefs['site_language'] = $prefs['language'] = $_REQUEST['lang'];
} else {
	$language = $prefs['site_language'] = $prefs['language'] = 'en';
}
include_once('lib/init/tra.php');

$local_php = TikiInit::getCredentialsFile();
$re = false;
if ( file_exists($local_php) ) {
	$re = include($local_php);
}

global $systemConfiguration;
$systemConfiguration = new Zend_Config(
	array(
		'preference' => array(
			'feature_jison_wiki_parser' => 'n',		// hard code json parser off, as it's more than just "experimental"
													// Developer Notice:
													// if you want to help improve this feature then either comment out the line above
													// or add 'feature_jison_wiki_parser' = 'y' to your tiki.ini file
													// and enable that in your db/local.php
		),
		'rules' => array(),
	),
	array('readOnly' => false)
);
if (isset ($system_configuration_file)) {
	if (! is_readable($system_configuration_file)) {
		die('Configuration file could not be read.');
	}
	if (! isset($system_configuration_identifier)) {
		$system_configuration_identifier = null;
	}
	$systemConfiguration = $systemConfiguration->merge(new Zend_Config_Ini($system_configuration_file, $system_configuration_identifier));
}

if ( empty( $api_tiki ) ) {
	$api_tiki_forced = false;
	$api_tiki = $default_api_tiki;
} else {
	$api_tiki_forced = true;
}

if ( $re === false ) {
	if ( ! isset($in_installer) || $in_installer != 1) {
		header('location: tiki-install.php');
		exit;
	} else {
		// we are in the installer don't redirect...
		return ;
	}
}

if ( $dbversion_tiki == '1.10' ) {
	$dbversion_tiki = '2.0';
}

/**
 *
 */
class TikiDb_LegacyErrorHandler implements TikiDb_ErrorHandler
{
    /**
     * @param TikiDb $db
     * @param $query
     * @param $values
     * @param $result
     */
    function handle( TikiDb $db, $query, $values, $result ) // {{{
	{
		global $smarty, $prefs;

		$msg = $db->getErrorMessage();
		$q=$query;
		if (is_array($values)) {
			foreach ($values as $v) {
				if (is_null($v)) {
					$v = 'NULL';
				} else {
					$v = "'" . addslashes($v) . "'";
				}
				$pos = strpos($q, '?');
				if ($pos !== false) {
					$q = substr($q, 0, $pos) . "$v" . substr($q, $pos + 1);
				}
			}
		}

		if (function_exists('xdebug_get_function_stack')) {
            /**
             * @param $stack
             * @return string
             */
            function mydumpstack($stack)
			{
				$o='';
				foreach ($stack as $line) {
					$o.='* '.$line['file']." : ".$line['line']." -> ".$line['function']."(".var_export($line['params'], true).")<br />";
				}
				return $o;
			}
			$stacktrace = mydumpstack(xdebug_get_function_stack());
		} else {
			$stacktrace = false;
		}

		require_once 'installer/installlib.php';
		$installer = new Installer;

		require_once('tiki-setup.php');

		$smarty->assign('msg', $msg);
		$smarty->assign('base_query', $query);
		$smarty->assign('values', $values);
		$smarty->assign('built_query', $q);
		$smarty->assign('stacktrace', $stacktrace);
		$smarty->assign('requires_update', $installer->requiresUpdate());

		header("Cache-Control: no-cache, pre-check=0, post-check=0");

		$smarty->display('database-connection-error.tpl');
		$this->log($msg.' - '.$q);
		die;
	} // }}}
    /**
     * @param $msg
     */
    function log($msg)
	{
		global $user, $tikilib;
		$query = 'insert into `tiki_actionlog` (`objectType`,`action`,`object`,`user`,`ip`,`lastModif`, `comment`, `client`) values (?,?,?,?,?,?,?,?)';
		$result = $tikilib->query($query, array('system', 'db error', 'system', $user, $tikilib->get_ip_address(), $tikilib->now, $msg, substr($_SERVER['HTTP_USER_AGENT'], 0, 200)));
	} // }}}
}

$dbInitializer = 'db/tiki-db-adodb.php';
if ($api_tiki == 'pdo' && extension_loaded("pdo") && in_array('mysql', PDO::getAvailableDrivers())) {
	$dbInitializer = 'db/tiki-db-pdo.php';
}

require $dbInitializer;
init_connection(TikiDb::get());

if ( isset( $shadow_host, $shadow_user, $shadow_pass, $shadow_dbs ) ) {
	global $dbMaster, $dbSlave;
	// Set-up the replication
	$dbMaster = TikiDb::get();

	$host_tiki = $shadow_host;
	$user_tiki = $shadow_user;
	$pass_tiki = $shadow_pass;
	$dbs_tiki = $shadow_dbs;
	require $dbInitializer;
	$dbSlave = TikiDb::get();
	init_connection($dbSlave);

	$db = new TikiDb_MasterSlaveDispatch($dbMaster, $dbSlave);
	TikiDb::set($db);
}

unset($host_map, $db_tiki, $host_tiki, $user_tiki, $pass_tiki, $dbs_tiki, $shadow_user, $shadow_pass, $shadow_host, $shadow_dbs);

/**
 * @param $db
 */
function init_connection( $db )
{
	global $db_table_prefix, $common_users_table_prefix, $db_tiki;

	$db->setServerType($db_tiki);
	$db->setErrorHandler(new TikiDb_LegacyErrorHandler);

	if ( isset( $db_table_prefix ) ) {
		$db->setTablePrefix($db_table_prefix);
	}

	if ( isset( $common_users_table_prefix ) ) {
		$db->setUsersTablePrefix($common_users_table_prefix);
	}
}
