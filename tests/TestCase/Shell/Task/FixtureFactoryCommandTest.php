<?php
namespace CakephpFixtureFactories\Test\TestCase\Shell\Task;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Command\FixtureFactoryCommand;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Shell\Task\FixtureFactoryTask;
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
class TestFixtureFactoryCommandTest extends TestCase
{
    /**
     * ConsoleIo mock
     *
     * @var ConsoleIo
     */
    public $io;
    /**
     * @var Arguments
     */
    public $args;

    /**
     * Test subject
     *
     * @var FixtureFactoryCommand
     */
    public $FactoryCommand;

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
        $this->io = new ConsoleIo();
        $this->FactoryCommand = new FixtureFactoryCommand();
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
        unset($this->FactoryCommand);

        parent::tearDown();
    }



    public function testFileName()
    {
        $name = 'Model';
        $this->assertSame('ModelFactory.php', $this->FactoryCommand->fileName($name));
    }

    public function testGetFactoryNameFromModelName()
    {
        $model = 'Apples';
        $this->assertEquals('AppleFactory', $this->FactoryCommand->getFactoryNameFromModelName($model));
    }

    public function testGetTableListInApp()
    {
        $this->assertEquals($this->appTables, $this->FactoryCommand->getTableList());
    }

    public function testGetTableListInPlugin()
    {
        $this->FactoryCommand->plugin = $this->testPluginName;
        $this->assertEquals($this->pluginTables, $this->FactoryCommand->getTableList());
    }

    public function testHandleAssociationsWithArticles()
    {
        $associations = $this->FactoryCommand->setTable('Articles', $this->io)->getAssociations();
        $expected = [
            'toOne' => [],
            'toMany' => ['Bills' => '\TestPlugin\Test\Factory\BillFactory', 'Authors' => '\TestApp\Test\Factory\AuthorFactory']
        ];
        $this->assertEquals($expected, $associations);
    }
    public function testHandleAssociationsWithAuthors()
    {
        $associations = $this->FactoryCommand->setTable('Authors', $this->io)->getAssociations();
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
        $associations = $this->FactoryCommand->setTable('Addresses',  $this->io)->getAssociations();
        $expected = [
            'toOne' => ['City' => '\TestApp\Test\Factory\CityFactory'],
            'toMany' => ['Author' => '\TestApp\Test\Factory\AuthorFactory',],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBillsWithoutPlugin()
    {
        $associations = $this->FactoryCommand->setTable('Bills',  $this->io)->getAssociations();
        $expected = [
            'toOne' => [],
            'toMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBills()
    {
        $this->FactoryCommand->plugin = $this->testPluginName;
        $associations = $this->FactoryCommand->setTable('Bills',  $this->io)->getAssociations();

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
            $this->FactoryCommand->getFactoryNamespace()
        );
    }

    public function testGetFactoryNamespaceWithPlugin()
    {
        $this->FactoryCommand->plugin = $this->testPluginName;
        $this->assertEquals(
            $this->testPluginName . '\Test\Factory',
            $this->FactoryCommand->getFactoryNamespace()
        );
    }

    public function testBakeUnexistingTable()
    {
        try {
            $this->assertFalse($this->FactoryCommand->setTable('oups',  $this->io));
        } catch (\Cake\Console\Exception\StopException $e) {
            $this->assertTextEquals("Cannot describe oups. It has 0 columns.", $e->getMessage());
        }
    }

    public function testRunBakeWithNoArguments()
    {
        $args = new Arguments([], [], []);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));
    }

    public function testRunBakeWithWrongModel()
    {
        $args = new Arguments(['model' => 'SomeModel'], [], []);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));
    }

    public function testRunBakeWithModel()
    {
        $args = new Arguments(['Articles'], ['force' => true], ['model']);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));

        $title = 'Foo';
        $articleFactory = ArticleFactory::make(compact('title'));
        $this->assertInstanceOf(BaseFactory::class, $articleFactory);

        $article = $articleFactory->persist();
        $this->assertEquals($title, $article->title);
    }
    public function testRunBakeAllInTestApp()
    {
        $args = new Arguments([], ['force' => true, 'all' => true], ['model']);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));

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
        $args = new Arguments(['Articles'], ['force' => true], ['model']);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));

        $args = new Arguments([], ['force' => true, 'plugin' => 'TestPlugin', 'all' => true], ['model']);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));

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
}
