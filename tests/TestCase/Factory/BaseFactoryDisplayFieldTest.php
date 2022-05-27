<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.6.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use Faker\Generator;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Author;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Country;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\CountriesTable;
use TestPlugin\Model\Entity\Bill;
use TestPlugin\Model\Table\BillsTable;
use function count;
use function is_array;
use function is_int;

class BaseFactoryDisplayFieldTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass()
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function testUseDisplayFieldIfFieldIsNotSpecified()
    {
        $title = 'Some title';
        $article = ArticleFactory::make('Some title')->getEntity();

        $this->assertSame($title, $article->title);
    }

    public function testUseDisplayFieldIfFieldIsNotSpecified_Multiple()
    {
        $titles = ['Some title 1', 'Some title 2'];
        $articles = ArticleFactory::make($titles)->getEntities();

        foreach ($titles as $i => $title) {
            $this->assertSame($title, $articles[$i]->title);
        }
    }

    public function testUseDisplayFieldInAssociationIfFieldIsNotSpecified()
    {
        $country = 'India';
        $address = AddressFactory::make()->with('City.Country', $country)->getEntity();

        $this->assertSame($country, $address->city->country->name);
    }

    public function testUseDisplayFieldInAssociationIfFieldIsNotSpecified_Multiple()
    {
        $cities = ['Chennai', 'Jodhpur', 'Kolkata'];
        $country = CountryFactory::make()->with('Cities', $cities)->getEntity();

        foreach ($cities as $i => $city) {
            $this->assertSame($city, $country->cities[$i]->name);
        }
    }
}
