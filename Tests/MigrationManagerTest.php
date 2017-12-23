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
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Mindy\Component\MigrationManager\MigrationManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class MigrationManagerTest extends TestCase
{
    /**
     * @var Connection
     */
    protected $connection;
    /**
     * @var MigrationManager
     */
    protected $manager;

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__.'/Migrations');

        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:']);
        $this->manager = new MigrationManager($this->connection, 'test', __DIR__, 'test');
    }

    protected function tearDown()
    {
        if ($this->connection) {
            $this->connection->close();
        }
        $this->connection = null;
    }

    public function testNameNormalization()
    {
        $this->assertSame('test_foo_bar_migrations', $this->manager->doNormalizeName('testFooBar'));
        $this->assertSame('hello_world_migrations', $this->manager->doNormalizeName('HelloWorld'));
        $this->assertSame('example_migrations', $this->manager->doNormalizeName('Example'));
    }

    public function testConfiguration()
    {
        $this->assertSame(sprintf("%s/Migrations", __DIR__), $this->manager->getMigrationDirectory());

        $configuration = $this->manager->getConfiguration();
        $this->assertInstanceOf(Configuration::class, $configuration);
        $this->assertSame('test_migrations', $configuration->getMigrationsTableName());

        $this->assertSame(__DIR__.'/Migrations', $configuration->getMigrationsDirectory());
    }

    public function testMigrate()
    {
        $this->assertTrue($this->manager->doMigrate('first', true));
    }

    /**
     * @expectedException \Doctrine\DBAL\Migrations\MigrationException
     * @expectedExceptionMessage Unknown version: foobar
     */
    public function testUnknownVersion()
    {
        $this->assertTrue($this->manager->doMigrate('foobar', true));
    }

    /**
     * @expectedException \Doctrine\DBAL\Migrations\MigrationException
     * @expectedExceptionMessage Already at first version.
     */
    public function testLastVersion()
    {
        $this->assertTrue($this->manager->doMigrate('prev', true));
    }

    /**
     * @expectedException \Doctrine\DBAL\Migrations\MigrationException
     * @expectedExceptionMessage Already at latest version.
     */
    public function testNextVersion()
    {
        $this->assertTrue($this->manager->doMigrate('next', true));
    }

    /**
     * @expectedException \Doctrine\DBAL\Migrations\MigrationException
     */
    public function testWriteSql()
    {
        $this->assertTrue($this->manager->writeSql('first', true));
    }

    public function testGenerateMigration()
    {
        $content = '<?php echo "hello world";';
        $this->assertSame(
            file_get_contents($this->manager->generateMigration($content)),
            $content
        );
    }
}
