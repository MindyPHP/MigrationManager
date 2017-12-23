<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Component\MigrationManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\MigrationException;

class MigrationManager
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * MigrationManager constructor.
     *
     * @param Connection $connection
     * @param string     $name
     * @param string     $path
     * @param string     $namespace
     */
    public function __construct(Connection $connection, string $name, string $path, string $namespace)
    {
        $this->configuration = $this->doBuildConfiguration($connection, $name, $path, $namespace);
    }

    /**
     * Create a new migration instance to execute the migrations.
     *
     * @return Migration a new migration instance
     */
    protected function createMigration(): Migration
    {
        return new Migration($this->configuration);
    }

    /**
     * @param string      $version
     * @param bool|string $path
     *
     * @return int
     */
    public function writeSql(string $version, $path)
    {
        return $this->createMigration()->writeSqlFile(
            is_bool($path) ? getcwd() : $path,
            $this->getVersionNameFromAlias($version)
        );
    }

    /**
     * @param string $version
     * @param bool   $dryRun
     *
     * @return bool
     */
    public function doMigrate(string $version, bool $dryRun): bool
    {
        $migration = $this->createMigration();
        $migration->setNoMigrationException(true);
        $migration->migrate($this->getVersionNameFromAlias($version), $dryRun, true);

        return true;
    }

    /**
     * @param Connection $connection
     * @param string     $name
     * @param string     $path
     * @param string     $namespace
     *
     * @return Configuration
     */
    public function doBuildConfiguration(Connection $connection, string $name, string $path, string $namespace): Configuration
    {
        $configuration = new Configuration($connection);
        $configuration->setName($name);
        $configuration->setMigrationsTableName($this->doNormalizeName($name));

        $configuration->setMigrationsNamespace(sprintf(
            '%s\Migrations',
            $namespace
        ));

        $migrationPath = sprintf('%s/Migrations', $path);
        $this->createDirIfNotExists($migrationPath);

        $configuration->setMigrationsDirectory($migrationPath);

        return $configuration;
    }

    /**
     * @param string $versionAlias
     *
     * @throws MigrationException
     *
     * @return string
     */
    private function getVersionNameFromAlias($versionAlias): string
    {
        $version = $this->configuration->resolveVersionAlias($versionAlias);
        if ($version === null) {
            if ($versionAlias == 'prev') {
                throw new MigrationException('Already at first version.');
            }
            if ($versionAlias == 'next') {
                throw new MigrationException('Already at latest version.');
            }

            throw new MigrationException(sprintf(
                'Unknown version: %s',
                $versionAlias
            ));
        }

        return $version;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function doNormalizeName($name)
    {
        $cleanName = str_replace('Bundle', '', $name);
        $normalizedName = trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $cleanName)), '_');

        return sprintf('%s_migrations', $normalizedName);
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param $dir
     */
    private function createDirIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * @param string $template
     * @param null   $up
     * @param null   $down
     *
     * @return string
     */
    public function generateMigration($template = null, $up = null, $down = null): string
    {
        if (empty($template)) {
            $template = file_get_contents(__DIR__.'/migration.php.template');
        }
        $version = $this->configuration->generateVersionNumber();
        $placeHolders = [
            '<namespace>',
            '<version>',
            '<up>',
            '<down>',
        ];
        $replacements = [
            $this->configuration->getMigrationsNamespace(),
            $version,
            $up ? '        '.implode("\n        ", explode("\n", $up)) : null,
            $down ? '        '.implode("\n        ", explode("\n", $down)) : null,
        ];
        $code = str_replace($placeHolders, $replacements, $template);
        $code = preg_replace('/^ +$/m', '', $code);
        $path = $this->configuration->getMigrationsDirectory().'/Version'.$version.'.php';

        file_put_contents($path, $code);

        return $path;
    }
}
