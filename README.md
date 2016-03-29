This migration will help you with updating database whith new versions of source code

# Installation

It's recommended that you use Composer to install InterventionSDK.

```bash
composer require bixev/migrations "~1.0"
```

This will install this library and all required dependencies.

so each of your php scripts need to require composer autoload file

```php
<?php

require 'vendor/autoload.php';
```

# Usage

## Basic usage

Use a given or custom migration store. It will store your migrations versions.

```php
$migrationsStore = new \Bixev\Migrations\VersionStore\MysqlVersionStore();
$migrationsStore->setDb(new PDO(''), 'table_name');
```

Instanciate migrations API

```php
$migrationsApi = new  \Bixev\Migrations\API($migrationsStore);
```

You have to give the api as many updaters as you have migrations file extensions

```php
$migrationsApi->setUpdater('php', new \Bixev\Migrations\Updater\PhpUpdater());
$migrationsApi->setUpdater('sql', new \Bixev\Migrations\Updater\MysqlUpdater());
```

Then, simply update with namespace and updates directory

```php
$migrationsApi->update('namespace', 'update/directory');
```

## Log

You can use logger to log update informations.

```php
$logger = new \Bixev\LightLogger\StdLogger();
$migrationsStore = new \Bixev\Migrations\VersionStore\MysqlVersionStore($logger);
$migrationsApi = new  \Bixev\Migrations\API($migrationsStore, $logger);
```