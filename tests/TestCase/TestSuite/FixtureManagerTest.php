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
namespace CakephpFixtureFactories\Test\TestCase\TestSuite;


use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\TestSuite\FixtureManager;
use CakephpFixtureFactories\TestSuite\Sniffer\MysqlTableSniffer;

class FixtureManagerTest extends TestCase
{
    /**
     * @var FixtureManager
     */
    public $FixtureManager;

    public function setUp()
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
        $this->assertEquals(1, $authors->firstOrFail()->id, 'The id should be equal to 1. There might be an error in the truncation of the authors table, or of the tables in general');
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