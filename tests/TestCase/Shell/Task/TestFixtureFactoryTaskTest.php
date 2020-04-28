<?php
namespace TestFixtureFactories\Test\TestCase\Shell\Task;

use TestFixtureFactories\Shell\Task\TestFixtureFactoryTask;
use Cake\TestSuite\TestCase;

/**
 * App\Shell\Task\FactoryTask Test Case
 */
class TestFixtureFactoryTaskTest extends TestCase
{
    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo|\PHPUnit_Framework_MockObject_MockObject
     */
    public $io;

    /**
     * Test subject
     *
     * @var \TestFixtureFactories\Shell\Task\TestFixtureFactoryTask
     */
    public $FactoryTask;

    /**
     * @var string
     */
    public $testPluginName = 'TestPlugin';

    public $appTables = [
        'Addresses',
        'Articles',
        'Authors',
        'Cities',
        'Countries'
    ];

    public $pluginTables = [
        'Bills',
        'Customers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();
        $this->FactoryTask = new TestFixtureFactoryTask($this->io);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->FactoryTask);

        parent::tearDown();
    }

    public function testFileName()
    {
        $name = 'Model';
        $this->assertSame('ModelFactory.php', $this->FactoryTask->fileName($name));
    }

    public function testGetFactoryNameFromModelName()
    {
        $model = 'Apples';
        $this->assertEquals('AppleFactory', $this->FactoryTask->getFactoryNameFromModelName($model));
    }

    public function testGetTableListInApp()
    {
        $this->FactoryTask->pathToTableDir = 'tests/TestApp/src/Model/Table';
        $this->assertEquals($this->appTables, $this->FactoryTask->getTableList());
    }

    public function testGetTableListInPlugin()
    {
        $this->FactoryTask->plugin = $this->testPluginName;
        $this->assertEquals($this->pluginTables, $this->FactoryTask->getTableList());
    }

    public function testHandleAssociationsWithArticles()
    {
        $associations = $this->FactoryTask->setTable('Articles')->getAssociations();
        $expected = [
            'toOne' => [],
            'toMany' => ['Bills' => '\TestPlugin\Test\Factory\BillFactory', 'Authors' => '\TestApp\Test\Factory\AuthorFactory']
        ];
        $this->assertEquals($expected, $associations);
    }
    public function testHandleAssociationsWithAuthors()
    {
        $associations = $this->FactoryTask->setTable('Authors')->getAssociations();
        $expected = [
            'toOne' => [
                'Address' => '\TestApp\Test\Factory\AddressFactory',
                'BusinessAddress' => '\TestApp\Test\Factory\AddressFactory',
            ],
            'toMany' => ['Articles' => '\TestApp\Test\Factory\ArticleFactory']
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithAddresses()
    {
        $associations = $this->FactoryTask->setTable('Addresses')->getAssociations();
        $expected = [
            'toOne' => ['City' => '\TestApp\Test\Factory\CityFactory'],
            'toMany' => ['Author' => '\TestApp\Test\Factory\AuthorFactory',],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBillsWithoutPlugin()
    {
        $associations = $this->FactoryTask->setTable('Bills')->getAssociations();
        $expected = [
            'toOne' => [],
            'toMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBills()
    {
        $this->FactoryTask->plugin = $this->testPluginName;
        $associations = $this->FactoryTask->setTable('Bills')->getAssociations();

        $expected = [
            'toOne' => ['Article' => '\TestApp\Test\Factory\ArticleFactory', 'Customer' => '\TestPlugin\Test\Factory\CustomerFactory'],
            'toMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testGetFactoryNamespace()
    {
        $this->assertEquals(
            'TestApp\Test\Factory',
            $this->FactoryTask->getFactoryNamespace()
        );
    }

    public function testGetFactoryNamespaceWithPlugin()
    {
        $this->FactoryTask->plugin = $this->testPluginName;
        $this->assertEquals(
            $this->testPluginName . '\Test\Factory',
            $this->FactoryTask->getFactoryNamespace()
        );
    }

    public function testBakeUnexistingTable()
    {
        $this->assertFalse($this->FactoryTask->setTable('oups'));
    }
}
