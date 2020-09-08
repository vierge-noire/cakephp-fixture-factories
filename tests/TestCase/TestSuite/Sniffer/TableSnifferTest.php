<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\TestCase\TestSuite\Sniffer;


use Cake\Database\Driver\Sqlite;
use Cake\Database\Exception;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\TestSuite\FixtureManager;
use CakephpFixtureFactories\TestSuite\Migrator;
use CakephpFixtureFactories\TestSuite\Sniffer\BaseTableSniffer;
use CakephpFixtureFactories\TestSuite\Sniffer\SqliteTableSniffer;

class TableSnifferTest extends TestCase
{
    /**
     * @var BaseTableSniffer
     */
    public $TableSniffer;

    /**
     * @var FixtureManager
     */
    public $FixtureManager;

    public function setUp(): void
    {
        $this->FixtureManager = new FixtureManager();
        $this->TableSniffer = $this->FixtureManager->getSniffer('test');
    }

    public function tearDown(): void
    {
        unset($this->TableSniffer);
        unset($this->FixtureManager);
        ConnectionManager::drop('test_dummy_connection');
        
        parent::tearDown();
    }

    private function createNonExistentConnection()
    {
        $config = ConnectionManager::getConfig('test');
        $config['database'] = 'dummy_database';
        ConnectionManager::setConfig('test_dummy_connection', $config);
    }

    /**
     * Following the convention, the TableSniffers must be the name of
     * the driver (e.g. Mysql)  + "TableSniffer"
     */
    public function testTableSnifferFinder()
    {
        $driver = explode('\\', getenv('DB_DRIVER'));
        $driver = array_pop($driver);
        $expectedClass = '\CakephpFixtureFactories\TestSuite\Sniffer\\' . $driver . 'TableSniffer';
        $this->assertInstanceOf($expectedClass, $this->TableSniffer);
    }

    /**
     * All tables should be clean before every test
     */
    public function testGetDirtyTablesAfterDroppingTables()
    {
        $this->assertEmpty($this->TableSniffer->getDirtyTables());
    }

    /**
     * Find dirty tables
     */
    public function testGetDirtyTablesAfterCreatingCustomer()
    {
        $expected = [
            'addresses',
            'articles',
            'articles_authors',
            'authors',
            'cities',
            'countries'
        ];
        ArticleFactory::make()->persist();
        $found = $this->TableSniffer->getDirtyTables();
        sort($found);
        $this->assertSame($expected, $found);
    }

    /**
     * After droping all tables, only the package migration table should remain
     * This should never be dropped
     *  We run the migrations in the end in order not to create interference with other tests
     */
    public function testGetAllTablesAfterDroppingAll()
    {
        $this->FixtureManager->dropTables('test');
        $this->assertSame([], $this->TableSniffer->getAllTables());
        Migrator::migrate();
    }

    /**
     * This list will need to be maintained as new tables are created or removed
     */
    public function testGetAllTables()
    {
        $expected = [
            'phinxlog',
            'test_plugin_phinxlog',
            'articles',
            'articles_authors',
            'addresses',
            'cities',
            'countries',
            'authors',
            'customers',
            'bills'
        ];
        $found = $this->TableSniffer->getAllTables();
        sort($expected);
        sort($found);
        $this->assertSame($expected, $found);
    }

    /**
     * If a DB is not created, the sniffers should throw an exception
     */
    public function testGetDirtyTablesOnNonExistentDB()
    {
        $this->createNonExistentConnection();
        $this->expectException(Exception::class);
        $this->FixtureManager->getSniffer('test_dummy_connection')->getDirtyTables();
    }

    /**
     * If a DB is not created, the sniffers should throw an exception
     */
    public function testGetAllTablesOnNonExistentDB()
    {
        $this->createNonExistentConnection();
        $sniffer = $this->FixtureManager->getSniffer('test_dummy_connection');
        if ($sniffer instanceof SqliteTableSniffer) {
            $this->expectNotToPerformAssertions();
        } else {
            $this->expectException(Exception::class);
        }
        $this->FixtureManager->getSniffer('test_dummy_connection')->getAllTables();
    }

    public function testDeleteWithForeignKey()
    {
        $connection = ConnectionManager::get('test');
        if ($connection->config()['driver'] === Sqlite::class) {
            $connection->execute('PRAGMA foreign_keys = ON;' );
        }
        $city = CityFactory::make()->withCountry()->persist();
        $country = $city->country;
        $Countries = TableRegistry::getTableLocator()->get('countries');

        $this->expectException(\PDOException::class);
        $Countries->delete($country);

        $this->assertSame(1, $Countries->find()->count());

        $Countries->delete($country);
        $this->assertSame(0, $Countries->find()->count());
    }
}