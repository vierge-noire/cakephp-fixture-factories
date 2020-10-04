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
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpTestSuiteLight\FixtureInjector;
use CakephpTestSuiteLight\FixtureManager;

class FixtureInjectorTest extends TestCase
{
    /**
     * @var FixtureManager
     */
    public $FixtureManager;

    public function setUp()
    {
        $this->FixtureManager = new FixtureInjector(
            $this->createMock(FixtureManager::class)
        );
    }

    /**
     * 1. Create the config, no countries
     * 2. Run the test again, countries seeded. Remove config
     * 3. No countries left
     * @return array
     */
    public function feedRollBackAndMigrateIfRequired()
    {
        return [[1], [2], [3]];
    }

    /**
     * For each of the data provided, their should be
     * 10 Articles found, which is the last value given to times
     * value
     * @return array
     * @throws \Exception
     */
    public function createWithOneFactoryInTheDataProvider()
    {
        $Factory = ArticleFactory::make();
        return [
            [$Factory],
            [$Factory->setTimes(2)],
            [$Factory->setTimes(10)],
        ];
    }

    /**
     * For each test, a different factory is provided, so the expected
     * number of articles is the first parameter
     * @return array[]
     */
    public function createWithDifferentFactoriesInTheDataProvider()
    {
        return [
            [1, ArticleFactory::make()],
            [2, ArticleFactory::make(2)],
            [10, ArticleFactory::make(10)],
        ];
    }

    /**
     * @dataProvider feedRollBackAndMigrateIfRequired
     * @see \SeedCountries::up()
     * @see FixtureInjector::rollbackAndMigrateIfRequired()
     */
    public function testRollBackAndMigrateIfRequired($i)
    {
        $CountriesTable = TableRegistry::getTableLocator()->get('Countries');
        if ($i === 1) {
            Configure::write('TestFixtureMarkedNonMigrated', [[
                'source' => 'Seeds'
            ]]);
        }
        if ($i === 1 || $i === 3) {
            $this->assertSame(0, $CountriesTable->find()->count());
        } else {
            $this->assertSame(1, $CountriesTable->find()->count());
            $this->assertSame('Test Country', $CountriesTable->find()->first()->name);
            Configure::delete('TestFixtureMarkedNonMigrated');
        }
   }

    /**
     * Since there is only one factory in this data provider,
     * the factories will always return 10
     * @dataProvider createWithOneFactoryInTheDataProvider
     * @param $expected
     * @param ArticleFactory $factory
     * @throws \Exception
     */
    public function testCreateFactoryInTheDataProvider(ArticleFactory $factory)
    {
        $factory->persist();
        $this->assertSame(10, TableRegistry::getTableLocator()->get('Articles')->find()->count());
    }

    /**
     * Since there are distinct factories in this data provider,
     * the factories will produce different set of data
     * @dataProvider createWithDifferentFactoriesInTheDataProvider
     * @param $expected
     * @param ArticleFactory $factory
     * @throws \Exception
     */
    public function testCreateFactoryInTheDataProvider2(int $n, ArticleFactory $factory)
    {
        $factory->persist();
        $this->assertSame($n, TableRegistry::getTableLocator()->get('Articles')->find()->count());
    }
}