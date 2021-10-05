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
namespace CakephpFixtureFactories\Test\Scenario;


use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use TestApp\Model\Entity\Author;

class NAustralianAuthorsScenario implements FixtureScenarioInterface
{
    const COUNTRY_NAME = 'Australia';

    /**
     * @param mixed $args the number of authors
     * @return Author|Author[]
     */
    public function load(...$args)
    {
        $n = $args[0];
        return AuthorFactory::make($n)->fromCountry(self::COUNTRY_NAME)->persist();
    }
}
