<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;


use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\TestSuite\FixtureManager;
use CakephpFixtureFactories\TestSuite\Sniffer\MysqlTableSniffer;
use PHPUnit\Framework\TestCase;

class FixtureManagerTest extends TestCase
{
    /**
     * @var FixtureManager
     */
    public $FixtureManager;

    public function setUp(): void
    {
        $this->FixtureManager = new FixtureManager();
    }

    public function testTablePopulation()
    {
        $testName = 'Test Name';
        AuthorFactory::make(['name' => $testName])->persist();


        $authors = TableRegistry::getTableLocator()
            ->get('Authors')
            ->find();

        $this->assertEquals(1, $authors->count());
        $this->assertEquals(1, $authors->firstOrFail()->id);
    }

    public function testTablesEmptyOnStart()
    {
        $tables = ['addresses', 'articles', 'authors', 'cities', 'countries'];

        foreach ($tables as $table) {
            $Table = TableRegistry::getTableLocator()->get($table);
            $this->assertEquals(0, $Table->find()->count());
        }
    }

    public function testConnectionIsTest()
    {
        $this->assertEquals(
            'test',
            TableRegistry::getTableLocator()->get('Articles')->getConnection()->config()['name']
        );
    }

    public function testLoadBaseConfig()
    {
        $expected = MysqlTableSniffer::class;
        $this->FixtureManager->loadConfig();
        $conf = Configure::readOrFail('TestFixtureTableSniffers.' . \Cake\Database\Driver\Mysql::class);
        $this->assertEquals($expected, $conf);
    }

    public function testLoadCustomConfig()
    {
        $expected = '\testTableSniffer';
        $this->FixtureManager->loadConfig();
        $conf = Configure::readOrFail('TestFixtureTableSniffers.\testDriver');
        $this->assertEquals($expected, $conf);
    }
}