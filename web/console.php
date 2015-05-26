<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: console.php 45564 2013-04-18 02:57:52Z lphuberdeau $

use Symfony\Component\Console\Input\ArgvInput;

declare(ticks = 1); // how often to check for signals

if (function_exists('pcntl_signal')) {
	$exit = function () {
		error_reporting(0); // Disable error reporting, misleading backtrace on kill
		exit;
	};

	pcntl_signal(SIGTERM, $exit);
	pcntl_signal(SIGHUP,  $exit);
	pcntl_signal(SIGINT, $exit);
}


if (isset($_SERVER['REQUEST_METHOD'])) {
	die('Only available through command-line.');
}

require_once 'tiki-filter-base.php';
require_once 'lib/init/initlib.php';
include_once('lib/init/tra.php');
require_once('lib/setup/tikisetup.class.php');
require_once 'lib/setup/twversion.class.php';

$input = new ArgvInput;

if (false !== $site = $input->getParameterOption(array('--site'))) {
	$_SERVER['TIKI_VIRTUAL'] = $site;
}

$local_php = TikiInit::getCredentialsFile();

$console = new Tiki\Command\Application;

$console->add(new Tiki\Command\ConfigureCommand);
if (is_file($local_php)) {
	require 'db/tiki-db.php';
	$console->add(new Tiki\Command\InstallCommand);
	$console->add(new Tiki\Command\UpdateCommand);
} else {
	$console->add(new Tiki\Command\UnavailableCommand('database:install'));
	$console->add(new Tiki\Command\UnavailableCommand('database:update'));
}

if (is_file($local_php) && ($installer = new Installer) && ! $installer->requiresUpdate()) {
	require 'tiki-setup.php';
	$console->add(new Tiki\Command\CacheClearCommand);
	$console->add(new Tiki\Command\IndexRebuildCommand);
	$console->add(new Tiki\Command\IndexOptimizeCommand);
	$console->add(new Tiki\Command\IndexCatchUpCommand);
	$console->add(new Tiki\Command\ProfileForgetCommand);
	$console->add(new Tiki\Command\ProfileInstallCommand);
} else {
	$console->add(new Tiki\Command\UnavailableCommand('cache:clear'));
	$console->add(new Tiki\Command\UnavailableCommand('index:rebuild'));
	$console->add(new Tiki\Command\UnavailableCommand('index:optimize'));
	$console->add(new Tiki\Command\UnavailableCommand('index:catch-up'));
	$console->add(new Tiki\Command\UnavailableCommand('profile:forget'));
	$console->add(new Tiki\Command\UnavailableCommand('profile:install'));
}

$console->run();
