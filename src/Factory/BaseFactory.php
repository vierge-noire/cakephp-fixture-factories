<?php

namespace TestFixtureFactories\Factory;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use TestFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use Cake\Utility\Inflector;
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
    protected $associated = [];
    /**
     * @var array
     */
    protected $marshallerOptions = [
        'validate' => false,
        'forceNew' => true
    ];
    /**
     * @var array
     */
    protected $saveOptions = [
        'checkRules' => false,
        'atomic' => false,
        'checkExisting' => false
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
        $this->marshallerOptions = array_merge($this->marshallerOptions, $options);
        $this->rootTable = FactoryTableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
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
        return $this->rootTable->newEntity($this->data, $this->marshallerOptions);
    }

    /**
     * @return EntityInterface
     */
    public function persist()
    {
        // If the primary key is provided in the data, we do not
        // create the entity, but patch to the existing one
        debug($this->data);
        $primaryKey = $this->rootTable->getPrimaryKey();
        if (
            isset($this->data[$primaryKey]) &&
            $entity = $this->rootTable->find()->where([$this->rootTable->aliasField($primaryKey) => $this->data[$primaryKey]])->first()
        ) {
            $entity = $this->rootTable->patchEntity($entity, $this->data, $this->marshallerOptions);
        } else {
            $entity = $this->rootTable->newEntity($this->data, $this->marshallerOptions);
        }
        debug($entity);
        debug($this->getSaveOptions());
        $this->rootTable->saveOrFail($entity, $this->getSaveOptions());

        if (count($this->hasManyData) > 0) {
            foreach ($this->hasManyData as $association => $data) {
                $entity->{$association} = $data;
            }
            $this->rootTable->saveOrFail($entity, $this->getSaveOptions());
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

    public function getTable()
    {
        return $this->rootTable;
    }

    public function withOne(string $association, BaseFactory $factory): self
    {
        $this->data[$association] = $factory->getEntity()->toArray();

        $this->associated[] = Inflector::camelize($association);

        return $this;
    }

    public function getAssociated()
    {
        return $this->associated;
    }

    private function getSaveOptions()
    {
        return array_merge($this->saveOptions, [
            'associated' => $this->getAssociated()
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getRootTableRegistryName(): string;
}
