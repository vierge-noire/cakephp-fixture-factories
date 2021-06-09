<?php
namespace CakephpFixtureFactories\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use CakephpFixtureFactories\Shell\Task\FixtureFactoryTask;
use Cake\TestSuite\TestCase;

/**
 * App\Shell\Task\FactoryTask Test Case
 */
class FixtureFactoryTaskTest extends TestCase
{
    /**
     * ConsoleIo mock
     *
     * @var \Cake\Console\ConsoleIo
     */
    public $io;

    /**
     * Test subject
     *
     * @var \CakephpFixtureFactories\Shell\Task\FixtureFactoryTask
     */
    public $FactoryTask;

    /**
     * @var string
     */
    public $testPluginName = 'TestPlugin';

    public $appTables = [
        'Addresses',
        'ArticlesAuthors',
        'Articles',
        'Authors',
        'Cities',
        'Countries',
        'PremiumAuthors',
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
        $this->FactoryTask = new FixtureFactoryTask($this->io);
        $this->dropTestFactories();
    }

    private function dropTestFactories()
    {
        $factoryFolder = TESTS . 'Factory';
        array_map('unlink', glob("$factoryFolder/*.*"));
        $pluginFactoryFolder = Configure::read('App.paths.plugins')[0] . 'TestPlugin' . DS . 'tests' . DS . 'Factory';
        array_map('unlink', glob("$pluginFactoryFolder/*.*"));
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

    public function testGetTableListInApp()
    {
        $this->assertEquals($this->appTables, array_values($this->FactoryTask->getTableList()));
    }

    public function testGetTableListInPlugin()
    {
        $this->FactoryTask->plugin = $this->testPluginName;
        $this->assertEquals($this->pluginTables, array_values($this->FactoryTask->getTableList()));
    }

    public function testHandleAssociationsWithArticles()
    {
        $associations = $this->FactoryTask->setTable('Articles')->getAssociations();
        $expected = [
            'toOne' => [],
            'oneToMany' => [
                'Bills' => 'TestPlugin\Test\Factory\BillFactory',
                'ArticlesAuthors' => 'TestApp\Test\Factory\ArticlesAuthorFactory',
            ],
            'manyToMany' => [
                'Authors' => 'TestApp\Test\Factory\AuthorFactory',
                'ExclusivePremiumAuthors' => 'TestApp\Test\Factory\PremiumAuthorFactory'
            ]
        ];
        $this->assertEquals($expected, $associations);
    }
    public function testHandleAssociationsWithAuthors()
    {
        $associations = $this->FactoryTask->setTable('Authors')->getAssociations();
        $expected = [
            'toOne' => [
                'Address' => 'TestApp\Test\Factory\AddressFactory',
                'BusinessAddress' => 'TestApp\Test\Factory\AddressFactory'
            ],
            'oneToMany' => [],
            'manyToMany' => [
                'Articles' => 'TestApp\Test\Factory\ArticleFactory'
            ]
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithAddresses()
    {
        $associations = $this->FactoryTask->setTable('Addresses')->getAssociations();
        $expected = [
            'toOne' => [
                'City' => 'TestApp\Test\Factory\CityFactory'
            ],
            'oneToMany' => [
                'Authors' => 'TestApp\Test\Factory\AuthorFactory'
            ],
            'manyToMany' => []
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBillsWithoutPlugin()
    {
        $associations = $this->FactoryTask->setTable('Bills')->getAssociations();
        $expected = [
            'toOne' => [],
            'oneToMany' => [],
            'manyToMany' => []
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBills()
    {
        $this->FactoryTask->plugin = $this->testPluginName;
        $associations = $this->FactoryTask->setTable('Bills')->getAssociations();

        $expected = [
            'toOne' => [
                'Article' => 'TestApp\Test\Factory\ArticleFactory',
                'Customer' => 'TestPlugin\Test\Factory\CustomerFactory'
            ],
            'oneToMany' => [],
            'manyToMany' => []
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testBakeUnexistingTable()
    {
        $this->assertFalse($this->FactoryTask->setTable('oups'));
    }

    public function testRunBakeWithNoArguments()
    {
        $this->assertEquals(0, $this->FactoryTask->main());
    }

    public function testRunBakeWithWrongModel()
    {
        $this->assertEquals(1, $this->FactoryTask->main('SomeModel'));
    }

    public function dataForTestThisTableShouldBeBaked()
    {
        return [
            ['Cities', null, true],
            ['Cities', true, false],
            ['Cities', 'TestPlugin', false],
            ['Bills', null, false],
            ['Bills', 'TestPlugin', true],
            ['AbstractApp', null, false],
            ['AbstractPlugin', 'TestPlugin', false],
        ];
    }

    /**
     * @dataProvider dataForTestThisTableShouldBeBaked
     * @param string $model
     * @param $plugin
     * @param bool $expected
     */
    public function testThisTableShouldBeBaked(string $model, $plugin, bool $expected)
    {
        $this->FactoryTask->plugin = $plugin;

        $this->assertSame($expected, $this->FactoryTask->thisTableShouldBeBaked($model));
    }

//    public function testRunBakeWithModel()
//    {
//        $this->assertEquals(1, $this->FactoryTask->main('Articles'));
//        $title = 'Foo';
//        $articleFactory = ArticleFactory::make(compact('title'));
//        $this->assertInstanceOf(BaseFactory::class, $articleFactory);
//
//        $article = $articleFactory->persist();
//        $this->assertEquals($title, $article->title);
//    }
//    public function testRunBakeAllInTestApp()
//    {
//        $this->assertEquals(1, $this->FactoryTask->main('all'));
//
//        $this->assertInstanceOf(BaseFactory::class, ArticleFactory::make());
//        $this->assertInstanceOf(BaseFactory::class, AddressFactory::make());
//        $this->assertInstanceOf(BaseFactory::class, AuthorFactory::make());
//        $this->assertInstanceOf(BaseFactory::class, CityFactory::make());
//        $this->assertInstanceOf(BaseFactory::class, CountryFactory::make());
//
//        $country = CountryFactory::make(['name' => 'Foo'])->persist();
//        $country->id = null;
//        $city = CityFactory::make(['name' => 'Foo'])->with('Country', CountryFactory::make($country->toArray()))->persist();
//        $city->id = null;
//        $address = AddressFactory::make(['street' => 'Foo'])->with('City', CityFactory::make($city->toArray()))->persist();
//        $address->id = null;
//        $author = AuthorFactory::make(['name' => 'Foo'])->with('Address', AddressFactory::make($address->toArray()))->persist();
//        $article = ArticleFactory::make(['title' => 'Foo'])->with('Authors', AuthorFactory::make($author->toArray()))->persist();
//
//        $this->assertInstanceOf(Article::class, $article);
//        $this->assertInstanceOf(Author::class, $author);
//        $this->assertInstanceOf(Address::class, $address);
//        $this->assertInstanceOf(City::class, $city);
//        $this->assertInstanceOf(Country::class, $country);
//    }
//
//    public function testRunBakeAllInTestPlugin()
//    {
//        $this->assertEquals(1, $this->FactoryTask->main('Articles'));
//        $this->FactoryTask->plugin = 'TestPlugin';
//        $this->assertEquals(1, $this->FactoryTask->main('all'));
//
//        $customer = CustomerFactory::make(['name' => 'Foo'])->persist();
//        $customer->id = null;
//        $article = ArticleFactory::make(['title' => 'Foo'])->persist();
//        $article->id = null;
//
//        $bill = BillFactory::make(['amount' => 100])
//            ->with('Customer', CustomerFactory::make($customer->toArray()))
//            ->with('Article', ArticleFactory::make($article->toArray()))
//            ->persist();
//
//        $this->assertInstanceOf(Article::class, $article);
//        $this->assertInstanceOf(Bill::class, $bill);
//        $this->assertInstanceOf(Customer::class, $customer);
//    }
}
