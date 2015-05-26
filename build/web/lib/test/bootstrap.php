<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: bootstrap.php 45626 2013-04-20 21:07:59Z lphuberdeau $

define('TIKI_IN_TEST', 1);
define('CUSTOM_ERROR_LEVEL', defined('E_DEPRECATED') ? E_ALL ^ E_DEPRECATED : E_ALL);

ini_set('display_errors', 'on');
error_reporting(CUSTOM_ERROR_LEVEL);

$paths = array(
		ini_get('include_path'),
		realpath('.'),
		realpath('../core'),
		realpath('../..'),
		realpath('core'),
		realpath('../pear'),
		);

ini_set('include_path', implode(PATH_SEPARATOR, $paths));

require_once __DIR__ . '/../../vendor/autoload.php';

if (!is_file(dirname(__FILE__) . '/local.php')) {
	die("\nYou need to setup a new database and create a local.php file for the test suite inside " . dirname(__FILE__) . "\n\n");
}

global $local_php, $api_tiki, $style_base;
$api_tiki = 'adodb';
$local_php = dirname(__FILE__) . '/local.php';
require_once($local_php);

$style_base = 'skeleton';

// Force autoloading
if (! class_exists('ADOConnection')) {
	die('AdoDb not found.');
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

// for now the unit test suite only works with adodb
// using pdo generate an error when phpunit tries to serialize the globals variables
// since it is not possible to serialize a PDO object
$dbTiki = ADONewConnection($db_tiki);

if (!@$dbTiki->Connect($host_tiki, $user_tiki, $pass_tiki, $dbs_tiki)) {
	die("\nUnable to connect to the database\n\n");
}

TikiDb::set(new TikiDb_Adodb($dbTiki));

// update db if needed
include_once ('installer/installlib.php');
$installer = new Installer;

if (!$installer->tableExists('tiki_preferences')) {
	echo "Installing Tiki database...\n";
	$installer->cleanInstall();
} else if ($installer->requiresUpdate()) {
	echo "Updating Tiki database...\n";
	$installer->update();
}

$pwd = getcwd();
chdir(dirname(__FILE__) . '/../..');
global $smarty;
require_once 'lib/init/smarty.php';
$smarty->addPluginsDir('../smarty_tiki/');
require_once 'lib/cache/cachelib.php';
require_once 'lib/tikilib.php';
require_once 'lib/wiki/wikilib.php';
require_once 'lib/userslib.php';
require_once 'lib/headerlib.php';
require_once 'lib/init/tra.php';
require_once 'lib/init/initlib.php';
require_once 'lib/tikiaccesslib.php';

global $tikilib;
$tikilib = new TikiLib;
$userlib = new UsersLib;
$_SESSION = array(
		'u_info' => array(
			'login' => null
			)
		);
chdir($pwd);

require_once(dirname(__FILE__) . '/TikiTestCase.php');
require_once(dirname(__FILE__) . '/TestableTikiLib.php');

global $systemConfiguration;
$systemConfiguration = new Zend_Config(
	array(
		'preference' => array(),
		'rules' => array(),
	),
	array('readOnly' => false)
);

global $user_overrider_prefs;
$user_overrider_prefs = array();
$prefs['language'] = 'en';
$prefs['site_language'] = 'en';
require_once 'lib/setup/prefs.php';

ini_set('display_errors', 'on');
error_reporting(CUSTOM_ERROR_LEVEL);
