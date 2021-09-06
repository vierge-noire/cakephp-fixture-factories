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

namespace CakephpFixtureFactories\Scenario;

use CakephpFixtureFactories\Error\FixtureScenarioException;
use CakephpFixtureFactories\Factory\FactoryAwareTrait;

trait ScenarioAwareTrait
{
    use FactoryAwareTrait;

    /**
     * Load a given fixture scenario
     *
     * @param string $scenario Name of the scenario or fully qualified class.
     * @param mixed ...$args Arguments passed to the scenario
     * @return mixed
     */
    public function loadFixtureScenario(string $scenario, ...$args)
    {
        if (!class_exists($scenario)) {
            // phpcs:disable
            @[$scenarioName, $plugin] = array_reverse(explode('.', $scenario));
            // phpcs:enable
            $scenarioNamespace = trim($this->getFactoryNamespace($plugin), 'Factory') . 'Scenario';
            $scenarioName = str_replace('/', '\\', $scenarioName);
            $scenario = $scenarioNamespace . '\\' . $scenarioName . 'Scenario';
        }

        if (!is_subclass_of($scenario, FixtureScenarioInterface::class)) {
            $msg = "The class {$scenario} must implement " . FixtureScenarioInterface::class;
            throw new FixtureScenarioException($msg);
        }

        /** @var \CakephpFixtureFactories\Scenario\FixtureScenarioInterface $scenario */
        $scenario = new $scenario();

        return $scenario->load(...$args);
    }
}
