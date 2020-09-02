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

namespace CakephpFixtureFactories\Factory;


use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;

class EventManager
{
    /**
     * @var array
     */
    private $listeningBehaviors;

    /**
     * @var array
     */
    private $listeningModelEvents = [];

    /**
     * @var array
     */
    private $defaultListeningBehaviors;

    /**
     * @var BaseFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $rootTableRegistryName;

    /**
     * @var Table
     */
    private $originalTable;

    private $ormEvents = [
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

    /**
     * DataCompiler constructor.
     * @param BaseFactory $factory
     * @param string $rootTableRegistryName
     */
    public function __construct(BaseFactory $factory, string $rootTableRegistryName)
    {
        $this->factory = $factory;
        $this->rootTableRegistryName = $rootTableRegistryName;
        $this->originalTable = TableRegistry::getTableLocator()->get($rootTableRegistryName);
        $this->setDefaultListeningBehaviors();
    }

    /**
     * Create a table cloned from the TableRegistry and per default without Model Events
     * @return Table
     */
    public function getTable(): Table
    {
        $tableRegistry = FactoryTableRegistry::getTableLocator()->get($this->rootTableRegistryName);
        $this->ignoreModelEvents($tableRegistry);
        return $tableRegistry;
    }

    /**
     * @param Table $table
     */
    public function ignoreModelEvents(Table $table)
    {
        foreach ($this->ormEvents as $ormEvent) {
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
     * @param $listener
     * @param string $ormEvent
     */
    private function processTableListener(Table $table, $listener, string $ormEvent)
    {
        if (!in_array($ormEvent, $this->listeningModelEvents)) {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @param Table $table
     * @param $listener
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
        return in_array($behavior, $this->defaultListeningBehaviors) && !$table->hasBehavior($behavior);
    }

    /**
     * @param Table $table
     * @param $listener
     * @param string $ormEvent
     */
    private function processListener(Table $table, $listener, string $ormEvent)
    {
        if ($listener instanceof Table) {
            $this->processTableListener($table, $listener, $ormEvent);
        } elseif ($listener instanceof Behavior) {
            $this->processBehaviorListener($table, $listener, $ormEvent);
        } else {
            $table->getEventManager()->off($ormEvent, $listener);
        }
    }

    /**
     * @return array
     */
    public function getListeningBehaviors(): array
    {
        return $this->listeningBehaviors;
    }

    /**
     * @param array|string $activeBehaviors
     */
    public function listeningToBehaviors($activeBehaviors)
    {
        $activeBehaviors = (array) $activeBehaviors;
        $this->listeningBehaviors = array_merge($this->defaultListeningBehaviors, $activeBehaviors);
    }

    /**
     * @param array|string $activeModelEvents
     */
    public function listeningToModelEvents($activeModelEvents)
    {
        $this->listeningModelEvents = (array) $activeModelEvents;
    }

    /**
     * @return array
     */
    public function getListeningModelEvents(): array
    {
        return $this->listeningModelEvents;
    }

    /**
     * @param array $defaultBehaviors
     */
    protected function setDefaultListeningBehaviors()
    {
        $defaultBehaviors = (array) Configure::read('TestFixtureGlobalBehavior', []);
        $defaultBehaviors[] = 'Timestamp';
        $this->defaultListeningBehaviors = $defaultBehaviors;
        $this->listeningBehaviors = $defaultBehaviors;
    }
}