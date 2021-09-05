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
use Cake\ORM\Query;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureScenarioException;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Scenario\FiveAustralianAuthorsScenario;
use CakephpFixtureFactories\Test\Scenario\TenAustralianAuthorsScenario;

class FixtureScenarioTest extends TestCase
{
    use ScenarioAwareTrait;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function scenarioNames(): array
    {
        return [
            ['FiveAustralianAuthors', FiveAustralianAuthorsScenario::N],
            [FiveAustralianAuthorsScenario::class, FiveAustralianAuthorsScenario::N],
            ['TenAustralianAuthors', 2*FiveAustralianAuthorsScenario::N],
            [TenAustralianAuthorsScenario::class, 2*FiveAustralianAuthorsScenario::N],
        ];
    }

    /**
     * @dataProvider scenarioNames
     */
    public function testLoadScenario($scenario, int $expectedAuthors)
    {
        $this->loadFixtureScenario($scenario);
        $this->assertSame($expectedAuthors, $this->countAustralianAuthors());
    }

    public function testLoadScenarioException()
    {
        $this->expectException(FixtureScenarioException::class);
        $this->loadFixtureScenario(self::class);
    }

    private function countAustralianAuthors(): int
    {
        return AuthorFactory::find()
            ->innerJoinWith('Address.City.Country', function (Query $q) {
                return $q->where(['Country.name' => FiveAustralianAuthorsScenario::COUNTRY_NAME]);
            })
            ->count();
    }
}
