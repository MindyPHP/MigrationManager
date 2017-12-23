# Migration Manager

[![Build Status](https://travis-ci.org/MindyPHP/MigrationManager.svg?branch=dev)](https://travis-ci.org/MindyPHP/MigrationManager)
[![codecov](https://codecov.io/gh/MindyPHP/MigrationManager/branch/master/graph/badge.svg)](https://codecov.io/gh/MindyPHP/MigrationManager)

## Установка

```
composer require mindy/migration-manager --prefer-dist
```

## Использование

```
<?php

use Doctrine\DBAL\DriverManager;
use Mindy\Component\MigrationManager\MigrationFactory;

$connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
$manager = MigrationFactory::createManager($connection, 'application', __DIR__.'/Migrations', '\\Application\Migrations');

// Generate new migration
$migrationPath = $manager->generateMigration();
echo $migrationPath . PHP_EOL;

// Migrate
$manager->doMigrate();
```
