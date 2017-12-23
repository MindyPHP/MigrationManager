<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Component\MigrationManager;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class MigrationFactory
{
    /**
     * @param Connection $connection
     * @param string     $name
     * @param string     $path
     *
     * @return MigrationManager
     */
    public static function createManager(Connection $connection, string $name, string $path, string $namespace): MigrationManager
    {
        return new MigrationManager($connection, $name, $path, $namespace);
    }

    /**
     * @param Connection      $connection
     * @param BundleInterface $bundle
     *
     * @return MigrationManager
     */
    public static function createManagerBundle(Connection $connection, BundleInterface $bundle): MigrationManager
    {
        return new MigrationManager(
            $connection,
            $bundle->getName(),
            $bundle->getPath(),
            (new \ReflectionClass($bundle))->getNamespaceName()
        );
    }
}
