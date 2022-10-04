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

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use TestDatabaseCleaner\TruncateDirtyTablesTrait;

class FixtureInjectorTest extends TestCase
{
    use TruncateDirtyTablesTrait;

    /**
     * For each of the data provided, their should be
     * 10 Articles found, which is the last value given to times
     * value
     *
     * @return array
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
     *
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
     * Since there is only one factory in this data provider,
     * the factories will always return 10
     *
     * @dataProvider createWithOneFactoryInTheDataProvider
     * @param ArticleFactory $factory
     */
    public function testCreateFactoryInTheDataProvider(ArticleFactory $factory)
    {
        $factory->persist();
        $this->assertSame(10, ArticleFactory::count());
    }

    /**
     * Since there are distinct factories in this data provider,
     * the factories will produce different set of data
     *
     * @dataProvider createWithDifferentFactoriesInTheDataProvider
     * @param int $n
     * @param ArticleFactory $factory
     */
    public function testCreateFactoryInTheDataProvider2(int $n, ArticleFactory $factory)
    {
        $factory->persist();
        $this->assertSame($n, ArticleFactory::count());
    }
}
