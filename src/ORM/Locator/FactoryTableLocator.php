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
namespace CakephpFixtureFactories\ORM\Locator;

use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Table;
use CakephpFixtureFactories\Event\ModelEventsHandler;

class FactoryTableLocator extends TableLocator
{
    protected function _create(array $options): Table
    {
        $options['CakephpFixtureFactoriesListeningModelEvents'] = $options['CakephpFixtureFactoriesListeningModelEvents'] ?? [];
        $options['CakephpFixtureFactoriesListeningBehaviors'] = $options['CakephpFixtureFactoriesListeningBehaviors'] ?? [];

        $cloneTable = parent::_create($options);

        ModelEventsHandler::handle(
            $cloneTable,
            $options['CakephpFixtureFactoriesListeningModelEvents'],
            $options['CakephpFixtureFactoriesListeningBehaviors']
        );

        return $cloneTable;
    }
}
