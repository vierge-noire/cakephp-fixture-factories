<?php
namespace CakephpFixtureFactories\Test\TestCase\Shell\Task;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Shell\Task\TestFixtureFactoryTask;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Author;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Country;
use TestApp\Test\Factory\AddressFactory;
use TestApp\Test\Factory\ArticleFactory;
use TestApp\Test\Factory\AuthorFactory;
use TestApp\Test\Factory\CityFactory;
use TestApp\Test\Factory\CountryFactory;
use TestPlugin\Model\Entity\Bill;
use TestPlugin\Model\Entity\Customer;
use TestPlugin\Test\Factory\BillFactory;
use TestPlugin\Test\Factory\CustomerFactory;

/**
 * App\Shell\Task\FactoryTask Test Case
 */
class TestFixtureFactoryTaskTest extends TestCase
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
     * @var \CakephpFixtureFactories\Shell\Task\TestFixtureFactoryTask
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
    public function setUp(): void
    {
        parent::setUp();
        $this->io = $this->getMockBuilder('Cake\Console\ConsoleIo')->getMock();
        $this->FactoryTask = new TestFixtureFactoryTask($this->io);
//        $this->FactoryTask->pathFragment = 'tests/TestApp/tests/Factory/';
//        $this->FactoryTask->pathToTableDir = 'tests/TestApp/src/Model/Table';
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
    public function tearDown(): void
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

    public function testRunBakeWithNoArguments()
    {
        $this->assertEquals(0, $this->FactoryTask->main());
    }

    public function testRunBakeWithWrongModel()
    {
        $this->assertEquals(1, $this->FactoryTask->main('SomeModel'));
    }

    public function testRunBakeWithModel()
    {
        $this->assertEquals(1, $this->FactoryTask->main('Articles'));
        $title = 'Foo';
        $articleFactory = ArticleFactory::make(compact('title'));
        $this->assertInstanceOf(BaseFactory::class, $articleFactory);

        $article = $articleFactory->persist();
        $this->assertEquals($title, $article->title);
    }
    public function testRunBakeAllInTestApp()
    {
        $this->assertEquals(1, $this->FactoryTask->main('all'));

        $this->assertInstanceOf(BaseFactory::class, ArticleFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AddressFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AuthorFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CityFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CountryFactory::make());

        $country = CountryFactory::make(['name' => 'Foo'])->persist();
        $country->id = null;
        $city = CityFactory::make(['name' => 'Foo'])->with('Country', CountryFactory::make($country->toArray()))->persist();
        $city->id = null;
        $address = AddressFactory::make(['street' => 'Foo'])->with('City', CityFactory::make($city->toArray()))->persist();
        $address->id = null;
        $author = AuthorFactory::make(['name' => 'Foo'])->with('Address', AddressFactory::make($address->toArray()))->persist();
        $article = ArticleFactory::make(['title' => 'Foo'])->with('Authors', AuthorFactory::make($author->toArray()))->persist();

        $this->assertInstanceOf(Article::class, $article);
        $this->assertInstanceOf(Author::class, $author);
        $this->assertInstanceOf(Address::class, $address);
        $this->assertInstanceOf(City::class, $city);
        $this->assertInstanceOf(Country::class, $country);
    }

    public function testRunBakeAllInTestPlugin()
    {
        $this->assertEquals(1, $this->FactoryTask->main('Articles'));
        $this->FactoryTask->plugin = 'TestPlugin';
        $this->assertEquals(1, $this->FactoryTask->main('all'));

        $customer = CustomerFactory::make(['name' => 'Foo'])->persist();
        $customer->id = null;
        $article = ArticleFactory::make(['title' => 'Foo'])->persist();
        $article->id = null;

        $bill = BillFactory::make(['amount' => 100])
            ->with('Customer', CustomerFactory::make($customer->toArray()))
            ->with('Article', ArticleFactory::make($article->toArray()))
            ->persist();

        $this->assertInstanceOf(Article::class, $article);
        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertInstanceOf(Customer::class, $customer);
    }

//    public function testPatchData()
//    {
//        $articles = \TestFixtureFactories\Test\Factory\ArticleFactory::make(null, 4)->setJobTitle()->persist();
//        dd(
//            $articles
//        );
//    }
}
