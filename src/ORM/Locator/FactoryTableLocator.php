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
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Factory\EventManager;

class FactoryTableLocator extends TableLocator
{
    public static $ormEvents = [
        'Model.initialize',
        'Model.beforeMarshal',
        'Model.afterMarshal',
        'Model.beforeFind',
        'Model.buildValidator',
        'Model.buildRules',
        'Model.beforeRules',
        'Model.afterRules',
        'Model.beforeSave',
        'Model.afterSave',
        'Model.afterSaveCommit',
        'Model.beforeDelete',
        'Model.afterDelete',
        'Model.afterDeleteCommit',
    ];

    protected function _create(array $options): Table
    {
        $cloneTable = parent::_create($options);

        $eventManager = $options['CakephpFixtureFactoriesEventManager'] ?? false;

        if ($eventManager) {
            /** @var EventManager $eventManager */
            $eventManager->ignoreModelEvents($cloneTable);
        } else {
            foreach (self::$ormEvents as $ormEvent) {
                foreach ($cloneTable->getEventManager()->listeners($ormEvent) as $listeners) {
                    if (array_key_exists('callable', $listeners) && is_array($listeners['callable'])) {
                        if ($listeners['callable'][0] instanceof TimestampBehavior) {
                            continue;
                        }
                    }
                    $cloneTable->getEventManager()->off($ormEvent, $listeners['callable']);
                }
            }
        }
        return $cloneTable;
    }
}
