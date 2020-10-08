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
namespace CakephpFixtureFactories\Test\TestCase\Command;

use Cake\Console\Exception\StopException;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Test\TestCase\Helper\TestCaseWithFixtureBaking;
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
class BakeFixtureFactoryCommandTest extends TestCaseWithFixtureBaking
{
    /**
     * @var string
     */
    public $testPluginName = 'TestPlugin';

    public $appTables = [
        'Addresses',
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

    public function testFileName()
    {
        $name = 'Model';
        $this->assertSame('ModelFactory.php', $this->FactoryCommand->fileName($name));
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
            'oneToMany' => ['Bills' => 'TestPlugin\Test\Factory\BillFactory'],
            'manyToMany' => [
                'Authors' => 'TestApp\Test\Factory\AuthorFactory',
                'ExclusivePremiumAuthors' => 'TestApp\Test\Factory\PremiumAuthorFactory',
            ],
        ];
        $this->assertEquals($expected, $associations);
    }
    public function testHandleAssociationsWithAuthors()
    {
        $associations = $this->FactoryCommand->setTable('Authors', $this->io)->getAssociations();
        $expected = [
            'toOne' => [
                'Address' => 'TestApp\Test\Factory\AddressFactory',
                'BusinessAddress' => 'TestApp\Test\Factory\AddressFactory',
            ],
            'oneToMany' => [],
            'manyToMany' => ['Articles' => 'TestApp\Test\Factory\ArticleFactory']
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithAddresses()
    {
        $associations = $this->FactoryCommand->setTable('Addresses',  $this->io)->getAssociations();
        $expected = [
            'toOne' => ['City' => 'TestApp\Test\Factory\CityFactory'],
            'oneToMany' => ['Authors' => 'TestApp\Test\Factory\AuthorFactory',],
            'manyToMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBillsWithoutPlugin()
    {
        $associations = $this->FactoryCommand->setTable('Bills',  $this->io)->getAssociations();
        $expected = [
            'toOne' => [],
            'oneToMany' => [],
            'manyToMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testHandleAssociationsWithBills()
    {
        $this->FactoryCommand->plugin = $this->testPluginName;
        $associations = $this->FactoryCommand->setTable('Bills',  $this->io)->getAssociations();

        $expected = [
            'toOne' => ['Article' => 'TestApp\Test\Factory\ArticleFactory', 'Customer' => 'TestPlugin\Test\Factory\CustomerFactory'],
            'oneToMany' => [],
            'manyToMany' => [],
        ];
        $this->assertEquals($expected, $associations);
    }

    public function testBakeUnexistingTable()
    {
        $this->expectException(StopException::class);
        $this->assertFalse($this->FactoryCommand->setTable('ignore_that',  $this->io));
    }

    public function testRunBakeWithNoArguments()
    {
        $this->bake();
    }

    public function testRunBakeWithWrongModel()
    {
        $this->bake(['model' => 'SomeModel']);
    }

    public function testRunBakeAllWithMethods()
    {
        $this->bake([], ['methods' => true, 'all' => true]);

        $title = 'Foo';
        $articleFactory = ArticleFactory::make(compact('title'))->withAuthors([], 2);
        $this->assertInstanceOf(ArticleFactory::class, $articleFactory);

        $article = $articleFactory->getEntity();
        $this->assertEquals($title, $article->title);
        $authors = $article->authors;
        $this->assertSame(2, count($authors));
        foreach ($authors as $author) {
            $this->assertInstanceOf(Author::class, $author);
        }
    }

    public function testRunBakeAllInTestAppWithMethods()
    {
        $this->bake([], ['all' => true, 'methods' => true,]);

        $this->assertInstanceOf(BaseFactory::class, ArticleFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AddressFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AuthorFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CityFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CountryFactory::make());

        $country = CountryFactory::make(['name' => 'Foo'])->persist();
        unset($country['id']);
        $city = CityFactory::make(['name' => 'Foo'])->withCountry($country->toArray())->persist();
        unset($city['id']);
        $address = AddressFactory::make(['street' => 'Foo'])->withCity($city->toArray())->persist();
        unset($address['id']);
        $author = AuthorFactory::make(['name' => 'Foo'])->withAddress($address->toArray())->persist();
        $article = ArticleFactory::make(['title' => 'Foo'])->withAuthors($author->toArray())->persist();
        $address2 = AddressFactory::make(['street' => 'Foo2'])->withCity($city->toArray())->withAuthors(['name' => 'Foo2'])->persist();


        $this->assertInstanceOf(Article::class, $article);
        $this->assertInstanceOf(Author::class, $author);
        $this->assertInstanceOf(Address::class, $address);
        $this->assertInstanceOf(Address::class, $address2);
        $this->assertInstanceOf(City::class, $city);
        $this->assertInstanceOf(Country::class, $country);
    }

    public function testRunBakeWithModel()
    {
        $this->bake(['Articles']);

        $title = 'Foo';
        $articleFactory = ArticleFactory::make(compact('title'));
        $this->assertInstanceOf(BaseFactory::class, $articleFactory);

        $article = $articleFactory->persist();
        $this->assertEquals($title, $article->title);
    }

    public function testRunBakeAllInTestApp()
    {
        $this->bake([], ['all' => true,]);

        $this->assertInstanceOf(BaseFactory::class, ArticleFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AddressFactory::make());
        $this->assertInstanceOf(BaseFactory::class, AuthorFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CityFactory::make());
        $this->assertInstanceOf(BaseFactory::class, CountryFactory::make());

        $country = CountryFactory::make(['name' => 'Foo'])->persist();
        unset($country['id']);
        $city = CityFactory::make(['name' => 'Foo'])->with('Country', CountryFactory::make($country->toArray()))->persist();
        unset($city['id']);
        $address = AddressFactory::make(['street' => 'Foo'])->with('City', CityFactory::make($city->toArray()))->persist();
        unset($address['id']);
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
        $this->bake(['Articles']);

        $this->bake([], ['plugin' => 'TestPlugin', 'all' => true,]);

        $customer = CustomerFactory::make(['name' => 'Foo'])->persist();
        unset($customer['id']);
        $article = ArticleFactory::make(['title' => 'Foo'])->persist();
        unset($article['id']);

        $bill = BillFactory::make(['amount' => 100])
            ->with('Customer', CustomerFactory::make($customer->toArray()))
            ->with('Article', ArticleFactory::make($article->toArray()))
            ->persist();

        $this->assertInstanceOf(Article::class, $article);
        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertInstanceOf(Customer::class, $customer);
    }
}
