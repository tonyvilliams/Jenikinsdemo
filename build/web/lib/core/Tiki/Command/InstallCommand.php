<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: InstallCommand.php 45723 2013-04-26 17:31:12Z changi67 $

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
	protected function configure()
	{
		$this
			->setName('database:install')
			->setDescription('Clean Tiki install')
			->addOption(
				'force',
				null,
				InputOption::VALUE_NONE,
				'Force installation. Overwrite any current database.'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$force = $input->getOption('force');
		$installer = new \Installer;
		$installed = $installer->tableExists('users_users');

		if (! $installed || $force) {
			$installer->cleanInstall();
			$output->writeln('Installation completed.');
			$output->writeln('<info>Queries executed successfully: ' . count($installer->success) . '</info>');

			if ( count($installer->failures) ) {
				foreach ( $installer->failures as $key => $error ) {
					list( $query, $message, $patch ) = $error;

					$output->writeln("<error>Error $key in $patch\n\t$query\n\t$message</error>");
				}
			}

			global $cachelib; require_once 'lib/cache/cachelib.php';
			$cachelib->empty_cache();
		} else {
			$output->writeln('<error>Database already exists.</error>');
		}
	}
}
