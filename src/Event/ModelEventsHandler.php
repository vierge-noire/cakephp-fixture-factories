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

namespace CakephpFixtureFactories\Event;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
use CakephpFixtureFactories\Factory\EventCollector;
use CakephpFixtureFactories\ORM\Locator\FactoryTableLocator;

class ModelEventsHandler
{
    /**
     * @var array
     */
    private $listeningBehaviors = [];

    /**
     * @var array
     */
    private $listeningModelEvents = [];

    /**
     * @var EventCollector
     */
    protected $eventCompiler;

    public static $ormEvents = [
        'Model.initialize',
        'Model.beforeMarshal',
        'Model.afterMarshal',
        'Model.beforeFind',
        'Model.buildValidator',
        'Model.buildRules',
        'Model.beforeFind',
        'Model.beforeRules',
        'Model.afterRules',
        'Model.beforeSave',
        'Model.afterSave',
        'Model.afterSaveCommit',
        'Model.beforeDelete',
        'Model.afterDelete',
        'Model.afterDeleteCommit',
    ];

    final public function __construct(array $listeningModelEvents, array $listeningBehaviors)
    {
        $this->listeningModelEvents = $listeningModelEvents;
        $this->listeningBehaviors = $listeningBehaviors;
    }

    public static function handle(Table $table, array $listeningModelEvents = [], array $listeningBehaviors = [])
    {
        $handler = new static($listeningModelEvents, $listeningBehaviors);
        $handler->ignoreModelEvents($table);
    }

    /**
     * @param Table $table
     */
    public function ignoreModelEvents(Table $table)
    {
        foreach (self::$ormEvents as $ormEvent) {
            foreach ($table->getEventManager()->listeners($ormEvent) as $listeners) {
                if (array_key_exists('callable', $listeners) && is_array($listeners['callable'])) {
                    foreach ($listeners['callable'] as $listener) {
                        $this->processListener($table, $listener, $ormEvent);
                    }
                }
            }
        }
    }

    /**
     * @param Table $table
     * @param mixed $listener
     * @param string $ormEvent
     */
    private function processListener(Table $table, $listener, string $ormEvent)
    {
        if ($listener instanceof Table) {
            $this->processModelListener($table, $listener, $ormEvent);
        } elseif ($listener instanceof Behavior) {
            $this->processBehaviorListener($table, $listener, $ormEvent);
        } else {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @param Table $table
     * @param mixed $listener
     * @param string $ormEvent
     */
    private function processModelListener(Table $table, $listener, string $ormEvent)
    {
        if (!in_array(
            $ormEvent,
            $this->getListeningModelEvents()
        )) {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @param Table $table
     * @param mixed $listener
     * @param string $ormEvent
     */
    private function processBehaviorListener(Table $table, $listener, string $ormEvent)
    {
        foreach ($this->getListeningBehaviors() as $behavior) {

            if ($this->skipBehavior($table, $behavior)) {
                continue;
            }

            $behavior = $table->getBehavior($behavior);
            $behavior = get_class($behavior);
            if ($listener instanceof $behavior) {
                return;
            }
        }
        $table->getEventManager()->off($ormEvent, $listener);
    }

    /**
     * Skip a behavior if it is in the default behavior list, and the
     * table does not have this behavior
     * @param Table $table
     * @param string $behavior
     * @return bool
     */
    private function skipBehavior(Table $table, string $behavior): bool
    {
        return in_array($behavior, $this->getListeningBehaviors()) && !$table->hasBehavior($behavior);
    }

    /**
     * @return array
     */
    public function getListeningModelEvents(): array
    {
        return $this->listeningModelEvents;
    }

    /**
     * @return array
     */
    public function getListeningBehaviors(): array
    {
        return $this->listeningBehaviors;
    }
}