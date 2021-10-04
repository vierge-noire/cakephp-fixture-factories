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
namespace CakephpFixtureFactories\Test\Scenario\SubFolder;


use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class SubFolderScenario implements FixtureScenarioInterface
{
    /**
     * Does nothing but proof that scenarios in subfolders are found
     *
     * @inheritDoc
     */
    public function load(...$args)
    {
    }
}
