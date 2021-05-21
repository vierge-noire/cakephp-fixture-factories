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

namespace CakephpFixtureFactories\Test\TestCase\Scenario;

use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureScenarioException;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use CakephpFixtureFactories\Test\Scenario\FiveAustralianAuthorsScenario;

class FixtureScenarioTest extends TestCase
{
    use ScenarioAwareTrait;

    /**
     * @var AuthorsTable
     */
    private $AuthorsTable;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function setUp(): void
    {
        $this->AuthorsTable     = TableRegistry::getTableLocator()->get('Authors');
        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->AuthorsTable);
        parent::tearDown();
    }

    public function scenarioNames(): array
    {
        return [
            ['FiveAustralianAuthors'],
            [FiveAustralianAuthorsScenario::class],
        ];
    }

    /**
     * @dataProvider scenarioNames
     */
    public function testLoadScenario($scenario)
    {
        $this->loadFixtureScenario($scenario);

        $australianAuthors = $this->AuthorsTable->find()
            ->innerJoinWith('Address.City.Country', function (Query $q) {
                return $q->where(['Country.name' => FiveAustralianAuthorsScenario::COUNTRY_NAME]);
            })
            ->count();
        $this->assertSame(FiveAustralianAuthorsScenario::N, $australianAuthors);
    }

    public function testLoadScenarioException()
    {
        $this->expectException(FixtureScenarioException::class);
        $this->loadFixtureScenario(self::class);
    }
}
