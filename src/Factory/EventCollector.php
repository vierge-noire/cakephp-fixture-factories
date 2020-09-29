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
use Cake\ORM\Table;
use CakephpFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;

class EventCollector
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
     * EventCollector constructor.
     * @param BaseFactory $factory
     * @param string $rootTableRegistryName
     */
    public function __construct(BaseFactory $factory, string $rootTableRegistryName)
    {
        $this->factory = $factory;
        $this->rootTableRegistryName = $rootTableRegistryName;
        $this->setDefaultListeningBehaviors();
    }

    /**
     * Create a table cloned from the TableRegistry and per default without Model Events
     * @return Table
     */
    public function getTable(): Table
    {
        $options = [];
        $options['CakephpFixtureFactoriesListeningModelEvents'] = $this->getListeningModelEvents() ?? [];
        $options['CakephpFixtureFactoriesListeningBehaviors'] = $this->getListeningBehaviors() ?? [];
        try {
            $table = FactoryTableRegistry::getTableLocator()->get($this->rootTableRegistryName, $options);
        } catch (\RuntimeException $exception) {
            FactoryTableRegistry::getTableLocator()->clear();
            $table = FactoryTableRegistry::getTableLocator()->get($this->rootTableRegistryName, $options);
        }
        return $table;
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
        $defaultBehaviors = (array) Configure::read('TestFixtureGlobalBehaviors', []);
        $defaultBehaviors[] = 'Timestamp';
        $this->defaultListeningBehaviors = $defaultBehaviors;
        $this->listeningBehaviors = $defaultBehaviors;
    }

    /**
     * @return array
     */
    public function getDefaultListeningBehaviors(): array
    {
        return $this->defaultListeningBehaviors;
    }
}