<?php

$logger = new \Bixev\LightLogger\StdLogger();
$migrationsStore = new \Bixev\Migrations\VersionStore\MysqlVersionStore($logger);
$migrationsStore->setDb(new PDO(''), 'table_name');
$migrationsApi = new  \Bixev\Migrations\API($migrationsStore, $logger);
$migrationsApi->setUpdater('php', new \Bixev\Migrations\Updater\PhpUpdater());
$migrationsApi->setUpdater('sql', new \Bixev\Migrations\Updater\MysqlUpdater());
$migrationsApi->update('namespace', 'update/directory');