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
use ReflectionException;
use ReflectionFunction;

/**
 * Class ModelEventsHandler
 *
 * @internal
 */
class ModelEventsHandler
{
    /**
     * @var array
     */
    private array $listeningBehaviors = [];

    /**
     * @var array
     */
    private array $listeningModelEvents = [];

    /**
     * @var \CakephpFixtureFactories\Factory\EventCollector
     */
    protected EventCollector $eventCompiler;

    public static array $ormEvents = [
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

    /**
     * ModelEventsHandler constructor.
     *
     * @param array $listeningModelEvents Model events listened to from instanciation
     * @param array $listeningBehaviors Behaviors listened to from instanciation
     * @return void
     */
    final public function __construct(array $listeningModelEvents, array $listeningBehaviors)
    {
        $this->listeningModelEvents = $listeningModelEvents;
        $this->listeningBehaviors = $listeningBehaviors;
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param array $listeningModelEvents Events listened to
     * @param array $listeningBehaviors Behaviors listened to
     * @return void
     */
    public static function handle(Table $table, array $listeningModelEvents = [], array $listeningBehaviors = []): void
    {
        (new static($listeningModelEvents, $listeningBehaviors))->ignoreModelEvents($table);
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @return void
     */
    private function ignoreModelEvents(Table $table): void
    {
        foreach (self::$ormEvents as $ormEvent) {
            foreach ($table->getEventManager()->listeners($ormEvent) as $listeners) {
                if (array_key_exists('callable', $listeners)) {
                    $this->processListener($table, $listeners['callable'], $ormEvent);
                }
            }
        }
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param mixed $listener Listener
     * @param string $ormEvent Event name
     * @return void
     */
    private function processListener(Table $table, mixed $listener, string $ormEvent): void
    {
        try {
            $reflection = new ReflectionFunction($listener);
            $obj = $reflection->getClosureThis();
        } catch (ReflectionException $e) {
            // Do something?
        }

        if (isset($obj)) {
            if ($obj instanceof Table) {
                $this->processModelListener($table, $obj, $ormEvent);
            } elseif ($obj instanceof Behavior) {
                $this->processBehaviorListener($table, $obj, $ormEvent);
            }
        } else {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param mixed $listener Listener
     * @param string $ormEvent Event Name
     * @return void
     */
    private function processModelListener(Table $table, mixed $listener, string $ormEvent): void
    {
        if (
            !in_array(
                $ormEvent,
                $this->getListeningModelEvents(),
            )
        ) {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @param \Cake\ORM\Table $table Table
     * @param mixed $listener Listener
     * @param string $ormEvent Event name
     * @return void
     */
    private function processBehaviorListener(Table $table, mixed $listener, string $ormEvent): void
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
     *
     * @param \Cake\ORM\Table $table Table
     * @param string $behavior Behavior name
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
