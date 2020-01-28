<?php

namespace TestFixtureFactories\Factory;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\TableRegistry;
use function array_merge;
use function count;

/**
 * Class BaseFactory
 *
 * @package TestFixtureFactories\Factory
 */
abstract class BaseFactory
{
    use LocatorAwareTrait;

    /**
     * @var \Cake\ORM\Table
     */
    protected $rootTable;
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var array
     */
    protected $options = [
        'validate' => false
    ];
    /**
     * hasMany Associations are saved after the main entity
     *
     * @var array
     */
    protected $hasManyData = [];
    /**
     * The reviewableBehavior is removed in the __construct if present.
     * Factories aim at seeding the DB. The Reviewable Behavior makes that seeding very complex.
     * This could be a temporary solution.
     * The behavior needs to be added after the entity has been generated.
     *
     * @var array
     */
    protected $reviewableBehaviorConfig;

    /**
     * BaseFactory constructor.
     *
     * @param array $data
     * @param array $options
     */
    protected function __construct(array $data = [], array $options = [])
    {
        $this->data = $data;
        $this->options = array_merge($this->options, $options);
        $this->rootTable = TableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        if ($this->rootTable->hasBehavior('Reviewable')) {
            $this->reviewableBehaviorConfig = $this->rootTable->getBehavior('Reviewable')->getConfig();
            $this->rootTable->removeBehavior('Reviewable');
        }
    }

    /**
     * @param array $data
     * @param array $options
     * @return static
     */
    public static function make(array $data = [], array $options = [])
    {
        return new static($data, $options);
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->rootTable->newEntity($this->data, $this->options);
    }

    /**
     * @return EntityInterface
     */
    public function persist()
    {
        // If the primary key is provided in the data, we do not
        // create the entity, but patch to the existing one
        $primaryKey = $this->rootTable->getPrimaryKey();
        if (
            isset($this->data[$primaryKey]) &&
            $entity = $this->rootTable->find()->where([$this->rootTable->aliasField($primaryKey) => $this->data[$primaryKey]])->first()
        ) {
            $entity = $this->rootTable->patchEntity($entity, $this->data, $this->options);
        } else {
            $entity = $this->rootTable->newEntity($this->data, $this->options);
        }

        $this->rootTable->saveOrFail($entity);

        if (count($this->hasManyData) > 0) {
            foreach ($this->hasManyData as $association => $data) {
                $entity->{$association} = $data;
            }
            $this->rootTable->saveOrFail($entity);
        }

        if (!$this->rootTable->hasBehavior('Reviewable') && $this->reviewableBehaviorConfig) {
            $this->rootTable->addBehavior('Datareview.Reviewable', $this->reviewableBehaviorConfig);
        }
        return $entity;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function mergeOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @return string
     */
    abstract protected function getRootTableRegistryName(): string;
}
