<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;


use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\TestSuite\FixtureManager;
use CakephpFixtureFactories\TestSuite\Migrator;
use CakephpFixtureFactories\TestSuite\Sniffer\BaseTableSniffer;
use CakephpFixtureFactories\TestSuite\Sniffer\TableSnifferFinder;
use PHPUnit\Framework\TestCase;

class TableSnifferTest extends TestCase
{
    use TableSnifferFinder;

    /**
     * @var BaseTableSniffer
     */
    public $TableSniffer;

    /**
     * @var FixtureManager
     */
    public $FixtureManager;

    public function setUp()
    {
        $connection = ConnectionManager::get('test');
        $driver = $connection->config()['driver'];
        $sniffer = Configure::readOrFail("TestFixtureTableSniffers.$driver");
        $this->TableSniffer = new $sniffer($connection);

        $this->FixtureManager = new FixtureManager();
    }

    public function tearDown(): void
    {
        unset($this->TableSniffer);
        unset($this->FixtureManager);
        
        parent::tearDown();
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
        $this->assertSame(['cakephp_fixture_factories_phinxlog'], $this->TableSniffer->getAllTables());
        Migrator::migrate();
    }

    /**
     * This list will need to be maintained as new tables are created
     */
    public function testGetAllTables()
    {
        $expected = [
            'cakephp_fixture_factories_phinxlog',
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
        $this->assertSame(sort($expected), sort($found));
    }
}