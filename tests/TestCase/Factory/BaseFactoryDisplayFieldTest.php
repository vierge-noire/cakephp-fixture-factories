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

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use TestPlugin\Model\Table\BillsTable;

class BaseFactoryDisplayFieldTest extends TestCase
{
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

    /**
     * It is important here to use the BillFactory in order to
     * cover the case where a factory is listening to some Model Events / Behavior
     * which resets the factories table.
     *
     * @see BillsTable::initialize()
     */
    public function testUseDisplayFieldErrorIfDisplayFieldAnArray()
    {
        $this->expectException(FixtureFactoryException::class);
        $expectedMessage = "The display field of a table must be a string when injecting a string into its factory. You injected 'Some bill' in CakephpFixtureFactories\Test\Factory\BillFactory but TestPlugin\Model\Table\BillsTable's display field is not a string.";
        $this->expectExceptionMessage($expectedMessage);
        BillFactory::make('Some bill')->persist();
    }
}
