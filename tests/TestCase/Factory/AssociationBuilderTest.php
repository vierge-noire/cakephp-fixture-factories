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

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\Core\Configure;
use Cake\ORM\Association;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use CakephpFixtureFactories\Factory\AssociationBuilder;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Table\AddressesTable;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\AuthorsTable;
use TestApp\Model\Table\CitiesTable;
use TestApp\Model\Table\CountriesTable;
use TestPlugin\Model\Table\BillsTable;
use TestPlugin\Model\Table\CustomersTable;

class AssociationBuilderTest extends TestCase
{
    /**
     * @var AssociationBuilder
     */
    public $associationBuilder;

    /**
     * @var AuthorsTable
     */
    private $AuthorsTable;

    /**
     * @var AddressesTable
     */
    private $AddressesTable;

    /**
     * @var ArticlesTable
     */
    private $ArticlesTable;

    /**
     * @var CountriesTable
     */
    private $CountriesTable;

    /**
     * @var CitiesTable
     */
    private $CitiesTable;

    /**
     * @var CustomersTable
     */
    private $CustomersTable;

    /**
     * @var BillsTable
     */
    private $BillsTable;

    public function setUp()
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');

        $this->AuthorsTable     = TableRegistry::getTableLocator()->get('Authors');
        $this->AddressesTable   = TableRegistry::getTableLocator()->get('Addresses');
        $this->ArticlesTable    = TableRegistry::getTableLocator()->get('Articles');
        $this->CountriesTable   = TableRegistry::getTableLocator()->get('Countries');
        $this->CitiesTable      = TableRegistry::getTableLocator()->get('Cities');
        $this->BillsTable       = TableRegistry::getTableLocator()->get('TestPlugin.Bills');
        $this->CustomersTable   = TableRegistry::getTableLocator()->get('TestPlugin.Customers');

        parent::setUp();
    }

    public function tearDown()
    {
        Configure::delete('TestFixtureNamespace');
        unset($this->associationBuilder);
        unset($this->AuthorsTable);
        unset($this->AddressesTable);
        unset($this->ArticlesTable);
        unset($this->CountriesTable);
        unset($this->CitiesTable);
        unset($this->BillsTable);
        unset($this->CustomersTable);

        parent::tearDown();
    }

    public function testCheckAssociationWithCorrectAssociation()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertInstanceOf(
            Association::class,
            $this->associationBuilder->getAssociation('Address')
        );
        $this->assertInstanceOf(
            Association::class,
            $this->associationBuilder->getAssociation('Address.City.Country')
        );
    }

    public function testCheckAssociationWithIncorrectAssociation()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $this->associationBuilder->getAssociation('Address.Country');
    }

    public function testGetFactoryFromTableName()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $street = 'Foo';
        $factory = $this->associationBuilder->getFactoryFromTableName('Address', compact('street'));
        $this->assertInstanceOf(AddressFactory::class, $factory);

        $address = $factory->persist();
        $this->assertSame($street, $address->street);

        $addresses = $this->AddressesTable->find();
        $this->assertSame(1, $addresses->count());
    }

    public function testGetFactoryFromTableNameWrong()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $this->associationBuilder->getFactoryFromTableName('Address.UnknownAssociation');
    }

    public function testGetAssociatedFactoryWithNoDepth()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $factory = $this->associationBuilder->getAssociatedFactory('Address');
        $this->assertInstanceOf(AddressFactory::class, $factory);
    }

    public function testGetAssociatedFactoryInPlugin()
    {
        $this->associationBuilder = new AssociationBuilder(ArticleFactory::make());

        $amount = 123;
        $factory = $this->associationBuilder->getAssociatedFactory('Bills', compact('amount'));
        $this->assertInstanceOf(BillFactory::class, $factory);

        $bill = $factory->persist();
        $this->assertEquals($amount, $bill->amount);

        $this->assertSame(1, $this->BillsTable->find()->count());
    }

    public function testValidateToOneAssociationPass()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertTrue(
            $this->associationBuilder->validateToOneAssociation('Articles', ArticleFactory::make(2))
        );
    }

    public function testValidateToOneAssociationFail()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $this->associationBuilder->validateToOneAssociation('Address', AddressFactory::make(2));
    }

    public function testRemoveBrackets()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $string = 'Authors[10].Address.City[10]';
        $expected = 'Authors.Address.City';

        $this->assertSame($expected, $this->associationBuilder->removeBrackets($string));
    }

    public function testGetTimeBetweenBracketsWithoutBrackets()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertNull($this->associationBuilder->getTimeBetweenBrackets('Authors'));
    }

    public function testGetTimeBetweenBracketsWith1Brackets()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $n = 10;
        $this->assertSame($n, $this->associationBuilder->getTimeBetweenBrackets("Authors[$n]"));
    }

    public function testGetTimeBetweenBracketsWithEmptyBrackets()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $this->associationBuilder->getTimeBetweenBrackets("Authors[]");
    }

    public function testGetTimeBetweenBracketsWith2Brackets()
    {
        $this->associationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $this->associationBuilder->getTimeBetweenBrackets("Authors[1][2]");
    }

    public function testBuildAssociationArrayForMarshaller()
    {
        $CityFactory = CityFactory::make();
        $this->associationBuilder = new AssociationBuilder($CityFactory);
        $CountryFactory = CountryFactory::make();
        $associated = $this->associationBuilder->buildAssociationArrayForMarshaller('Country', $CountryFactory);

        $this->assertSame(['Country'], $associated);
    }

    public function testBuildAssociationArrayForMarshallerDeep2()
    {
        $AddressFactory = AddressFactory::make()->with('City', CityFactory::make()->withCountry());

        $this->assertSame([
            'City',
            'City.Country'
        ], $AddressFactory->getAssociated());
    }

    public function testBuildAssociationArrayForMarshallerDeep3()
    {
        $AddressFactory = AddressFactory::make()->with('City', CityFactory::make()->with('Country', CountryFactory::make()->with('Cities')));

        $this->assertSame([
            'City',
            'City.Country',
            'City.Country.Cities',
            'City.Country.Cities.Country',
        ], $AddressFactory->getAssociated());
    }

    /**
     * The city associated to that primary country should belong to
     * the primary country
     */
    public function testRemoveAssociatedAssociationForToOneFactory()
    {
        $cityName = 'Foo';
        $CountryFactory = CountryFactory::make()->with(
            'Cities',
            CityFactory::make(['name' => $cityName])->withCountry()
        );

        $country = $CountryFactory->persist();

        $country = $this->CountriesTable->findById($country->id)->contain('Cities')->firstOrFail();

        $this->assertSame($cityName, $country->cities[0]->name);
    }
}