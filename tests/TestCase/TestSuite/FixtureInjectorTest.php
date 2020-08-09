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
namespace CakephpFixtureFactories\Test\TestCase\TestSuite;


use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\TestSuite\FixtureInjector;
use CakephpFixtureFactories\TestSuite\FixtureManager;
use CakephpFixtureFactories\TestSuite\Sniffer\MysqlTableSniffer;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Entity\Country;

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
}