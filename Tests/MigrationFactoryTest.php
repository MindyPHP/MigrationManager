<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Component\MigrationManager\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Mindy\Component\MigrationManager\MigrationFactory;
use Mindy\Component\MigrationManager\MigrationManager;
use PHPUnit\Framework\TestCase;

class MigrationFactoryTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    protected function setUp()
    {
        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
    }

    protected function tearDown()
    {
        if ($this->connection) {
            $this->connection->close();
        }
        $this->connection = null;
    }

    public function testFactoryDefault()
    {
        $manager = MigrationFactory::createManager($this->connection, 'testFooBar', __DIR__, 'test');
        $this->assertInstanceOf(MigrationManager::class, $manager);
    }

    public function testFactoryBundle()
    {
        $manager = MigrationFactory::createManagerBundle($this->connection, new TestBundle);
        $this->assertInstanceOf(MigrationManager::class, $manager);
    }
}
