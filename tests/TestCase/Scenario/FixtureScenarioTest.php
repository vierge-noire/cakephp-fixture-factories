<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakephpFixtureFactories\Test\TestCase\Scenario;

use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureScenarioException;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Scenario\NAustralianAuthorsScenario;
use CakephpFixtureFactories\Test\Scenario\SubFolder\SubFolderScenario;
use CakephpFixtureFactories\Test\Scenario\TenAustralianAuthorsScenario;
use TestApp\Model\Entity\Author;
use TestDatabaseCleaner\TruncateDirtyTablesTrait;

class FixtureScenarioTest extends TestCase
{
    use ScenarioAwareTrait;
    use TruncateDirtyTablesTrait;

    public static function setUpBeforeClass(): void
    {
        Configure::write('FixtureFactories.testFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('FixtureFactories.testFixtureNamespace');
    }

    public function scenarioNames(): array
    {
        return [
            ['NAustralianAuthors', 3],
            [NAustralianAuthorsScenario::class, 5],
            ['TenAustralianAuthors', 10],
            [TenAustralianAuthorsScenario::class, 10],
            ['SubFolder/SubFolder', 0],
            [SubFolderScenario::class, 0],
        ];
    }

    /**
     * @dataProvider scenarioNames
     */
    public function testLoadScenario($scenario, int $expectedAuthors)
    {
        /** @var Author[] $authors */
        $authors = $this->loadFixtureScenario($scenario, $expectedAuthors) ?? [];
        $this->assertSame($expectedAuthors, $this->countAustralianAuthors());
        foreach ($authors as $author) {
            $this->assertInstanceOf(Author::class, $author);
            $this->assertSame(
                NAustralianAuthorsScenario::COUNTRY_NAME,
                $author->address->city->country->name
            );
        }
    }

    /**
     * Throw an exception because this is not implementing the FixtureScenarioInterface
     */
    public function testLoadScenarioException()
    {
        $this->expectException(FixtureScenarioException::class);
        $this->loadFixtureScenario(self::class);
    }

    private function countAustralianAuthors(): int
    {
        return AuthorFactory::find()
            ->innerJoinWith('Address.City.Country', function (Query $q) {
                return $q->where(['Country.name' => NAustralianAuthorsScenario::COUNTRY_NAME]);
            })
            ->count();
    }
}
