<?php

$ftp = 'Your FTP connection';


return array(
	'my site' => array(
		'remote' => $ftp,
		'local' => '../',
		'test' => FALSE,
		'ignore' => '
			.git*
			/deployment
			/log/*
			!log/.htaccess
			temp/*
			!temp/.htaccess
			.htaccess
			/www/.htaccess
			.idea
			nbproject
			/database
            /tests
            /config/local.neon
            /HTML
            /bin
            /www/data
            /HTMLtemplates
            /www/node_modules/
            /www/dashboard/node_modules/
            /www/images/gallery
            /www/css/
            /temp/
            /www/dashboard/css
            /www/dashboard/js
            /www/upload/
            /config/*.json
            /app/database
		',
		'allowdelete' => TRUE,
		'before' => array(
			function (Deployment\Server $server, Deployment\Logger $logger, Deployment\Deployer $deployer) {
                $server->renameFile('www/index.php', 'www/index_OFF.php');
                $server->renameFile('www/.maintenance.php', 'www/index.php');
                $logger->log('Maintenance mode activated.');
			},
		),
		'after' => array(
            function (Deployment\Server $server, Deployment\Logger $logger, Deployment\Deployer $deployer) {
                $server->renameFile('www/index.php', 'www/.maintenance.php');
                $server->renameFile('www/index_OFF.php', 'www/index.php');
                $logger->log('Maintenance mode deactivated.');
            },
		),
		'purge' => array(
			'temp/cache',
		),
		'preprocess' => FALSE,
	),

	'tempdir' => __DIR__ . '/temp',
	'colors' => TRUE,
);
