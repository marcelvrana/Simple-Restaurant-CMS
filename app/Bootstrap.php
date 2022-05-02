<?php

declare(strict_types=1);

namespace App;

use Nette\Bootstrap\Configurator;

// absolute filesystem path to this web root
define('WWW_DIR', __DIR__ . '/../www');

// path to application folder
define('APP_DIR', __DIR__ . '/../app');

// absolute filesystem path to root
define('ROOT_DIR', realpath(WWW_DIR . '/..'));

class Bootstrap
{
	public static function boot(): Configurator
	{
		$configurator = new Configurator;
		$appDir = dirname(__DIR__);
		//$configurator->setDebugMode('secret@23.75.345.200'); // enable for your remote IP
		$configurator->enableTracy($appDir . '/log');

		$configurator->setTimeZone('Europe/Prague');
		$configurator->setTempDirectory($appDir . '/temp');

		$configurator->createRobotLoader()
			->addDirectory(__DIR__)
			->register();

		$configurator->addConfig($appDir . '/config/common.neon');
		$configurator->addConfig($appDir . '/config/local.neon');

		return $configurator;
	}
}
