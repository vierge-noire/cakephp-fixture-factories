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
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use CakephpFixtureFactories\Factory\AssociationBuilder;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class AssociationBuilderTest extends TestCase
{
    use TruncateDirtyTables;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function testCheckAssociationWithCorrectAssociation()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertInstanceOf(
            Association::class,
            $AssociationBuilder->getAssociation('Address')
        );
        $this->assertInstanceOf(
            Association::class,
            $AssociationBuilder->getAssociation('Address.City.Country')
        );
    }

    public function testCheckAssociationWithIncorrectAssociation()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $AssociationBuilder->getAssociation('Address.Country');
    }

    public function testGetFactoryFromTableName()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $street = 'Foo';
        $factory = $AssociationBuilder->getFactoryFromTableName('Address', compact('street'));
        $this->assertInstanceOf(AddressFactory::class, $factory);

        $address = $factory->persist();
        $this->assertSame($street, $address->street);
        $this->assertSame(1, AddressFactory::count());
    }

    public function testGetFactoryFromTableNameWrong()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $AssociationBuilder->getFactoryFromTableName('Address.UnknownAssociation');
    }

    public function testGetAssociatedFactoryWithNoDepth()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $factory = $AssociationBuilder->getAssociatedFactory('Address');
        $this->assertInstanceOf(AddressFactory::class, $factory);
    }

    public function testGetAssociatedFactoryInPlugin()
    {
        $AssociationBuilder = new AssociationBuilder(ArticleFactory::make());

        $amount = 123;
        $factory = $AssociationBuilder->getAssociatedFactory('Bills', compact('amount'));
        $this->assertInstanceOf(BillFactory::class, $factory);

        $bill = $factory->persist();
        $this->assertEquals($amount, $bill->amount);
        $this->assertSame(1, BillFactory::count());
    }

    public function testValidateToOneAssociationPass()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertTrue(
            $AssociationBuilder->validateToOneAssociation('Articles', ArticleFactory::make(2))
        );
    }

    public function testValidateToOneAssociationFail()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $AssociationBuilder->validateToOneAssociation('Address', AddressFactory::make(2));
    }

    public function testRemoveBrackets()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $string = 'Authors[10].Address.City[10]';
        $expected = 'Authors.Address.City';

        $this->assertSame($expected, $AssociationBuilder->removeBrackets($string));
    }

    public function testGetTimeBetweenBracketsWithoutBrackets()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->assertNull($AssociationBuilder->getTimeBetweenBrackets('Authors'));
    }

    public function testGetTimeBetweenBracketsWith1Brackets()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $n = 10;
        $this->assertSame($n, $AssociationBuilder->getTimeBetweenBrackets("Authors[$n]"));
    }

    public function testGetTimeBetweenBracketsWithEmptyBrackets()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());

        $this->expectException(AssociationBuilderException::class);
        $AssociationBuilder->getTimeBetweenBrackets('Authors[]');
    }

    public function testGetTimeBetweenBracketsWith2Brackets()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());
        $this->expectException(AssociationBuilderException::class);
        $AssociationBuilder->getTimeBetweenBrackets('Authors[1][2]');
    }

    public function testCollectAssociatedFactory()
    {
        $AssociationBuilder = new AssociationBuilder(CityFactory::make());
        $AssociationBuilder->collectAssociatedFactory('Country', CountryFactory::make());
        $expected = [
            'Country' => CountryFactory::make()->getMarshallerOptions(),
        ];
        $this->assertSame($expected, $AssociationBuilder->getAssociated());
    }

    public function testCollectAssociatedFactoryDeep2()
    {
        $AddressFactory = AddressFactory::make()->with(
            'City',
            CityFactory::make()->withCountry()
        );

        $expected = [
            'City' => CityFactory::make()->getMarshallerOptions() + [
                'associated' => [
                    'Country' =>  CountryFactory::make()->getMarshallerOptions(),
                ],
            ],
        ];
        $this->assertSame($expected, $AddressFactory->getAssociated());
    }

    public function testCollectAssociatedFactoryDeep3()
    {
        $AddressFactory = AddressFactory::make()->with(
            'City',
            CityFactory::make()->with(
                'Country',
                CountryFactory::make()->with('Cities')
            )
        );

        $expected = [
            'City' => [
                'validate' => false,
                'forceNew' => true,
                'accessibleFields' => ['*' => true],
                'associated' => [
                    'Country' => [
                        'validate' => false,
                        'forceNew' => true,
                        'accessibleFields' => ['*' => true],
                        'associated' => [
                            'Cities' => [
                                'validate' => false,
                                'forceNew' => true,
                                'accessibleFields' => ['*' => true],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $AddressFactory->getAssociated());
    }

    public function testDropAssociation()
    {
        $AssociationBuilder = new AssociationBuilder(AddressFactory::make());
        $AssociationBuilder->setAssociated(['City' => ['Country' => 'Foo']]);
        $AssociationBuilder->dropAssociation('City');
        $this->assertEmpty($AssociationBuilder->getAssociated());
    }

    public function testDropAssociationSingular()
    {
        $AssociationBuilder = new AssociationBuilder(AuthorFactory::make());
        $AssociationBuilder->setAssociated(['Authors']);
        $AssociationBuilder->dropAssociation('Author');
        $this->assertSame(['Authors'], $AssociationBuilder->getAssociated());
    }

    public function testDropAssociationDeep2()
    {
        $AssociationBuilder = new AssociationBuilder(AddressFactory::make());
        $AssociationBuilder->setAssociated(['City' => ['Country' => 'Foo', 'Bar']]);
        $AssociationBuilder->dropAssociation('City.Country');
        $this->assertSame(['City' => ['Bar']], $AssociationBuilder->getAssociated());
    }

    public function testCollectAssociatedFactoryWithoutAssociation()
    {
        $AddressFactory = AddressFactory::make()->without('City');

        $this->assertEmpty($AddressFactory->getAssociated());
    }

    public function testCollectAssociatedFactoryWithoutAssociationDeep2()
    {
        $AddressFactory = AddressFactory::make()->without('City.Country');

        $this->assertSame(['City' => CityFactory::make()->getMarshallerOptions()], $AddressFactory->getAssociated());
    }

    public function testCollectAssociatedFactoryWithBrackets()
    {
        $CityFactory = CityFactory::make()->with('Addresses[5]');

        $expected = [
            'Country' => [
                'validate' => false,
                'forceNew' => true,
                'accessibleFields' => ['*' => true],
            ],
            'Addresses' => [
                'validate' => false,
                'forceNew' => true,
                'accessibleFields' => ['*' => true],
            ],
        ];
        $this->assertSame($expected, $CityFactory->getAssociated());
    }

    public function testCollectAssociatedFactoryWithAliasedAssociation()
    {
        $ArticleFactory = ArticleFactory::make()
            ->with('ExclusivePremiumAuthors')
            ->without('Authors');

        $this->assertSame([
            'ExclusivePremiumAuthors' => [
                'validate' => false,
                'forceNew' => true,
                'accessibleFields' => ['*' => true],
                'associated' => [
                    'Address' => [
                        'validate' => false,
                        'forceNew' => true,
                        'accessibleFields' => ['*' => true],
                        'associated' => [
                            'City' => [
                                'validate' => false,
                                'forceNew' => true,
                                'accessibleFields' => ['*' => true],
                                'associated' => [
                                    'Country' => [
                                        'validate' => false,
                                        'forceNew' => true,
                                        'accessibleFields' => ['*' => true],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $ArticleFactory->getAssociated());
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

        $this->assertSame(['Cities' => [
            'validate' => false,
            'forceNew' => true,
            'accessibleFields' => ['*' => true],
        ]], $CountryFactory->getAssociated());

        $country = $CountryFactory->persist();

        $country = CountryFactory::find()->where(['id' => $country->id])->contain('Cities')->firstOrFail();

        $this->assertSame($cityName, $country->cities[0]->name);
    }
}
