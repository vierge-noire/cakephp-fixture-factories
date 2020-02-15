<?php

namespace TestFixtureFactories\Factory;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Cake\ORM\Locator\LocatorAwareTrait;
use TestFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use Cake\Utility\Inflector;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use RuntimeException;
use function array_merge;
use function count;
use function debug;
use function is_array;
use function is_callable;

/**
 * Class BaseFactory
 *
 * @TODO : add way to manage default values easily
 * @TODO : throw exception when passing $times > 1 on hasOne association
 *
 * @package TestFixtureFactories\Factory
 */
abstract class BaseFactory
{
    //use LocatorAwareTrait;

    /**
     * The number of records the factory should create
     *
     * @var int
     */
    private $times = 1;
    /**
     * @var Generator
     */
    static private $faker = null;
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
    private $templateData = [];
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
    protected function __construct()
    {
        $this->data = $data;
        $this->marshallerOptions = array_merge($this->marshallerOptions, $options);
        $this->rootTable = FactoryTableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        if ($this->rootTable->hasBehavior('Reviewable')) {
            $this->reviewableBehaviorConfig = $this->rootTable->getBehavior('Reviewable')->getConfig();
            $this->rootTable->removeBehavior('Reviewable');
        }
    }

    private function getFaker(): Generator
    {
        if (is_null(self::$faker)) {
            $faker = Factory::create();
            $faker->seed(1234);
            self::$faker = $faker;
        }

        return self::$faker;
    }

    /**
     * @param array|callable|null $data
     * @param array               $options
     * @return static
     */
    public static function make($makeParameter = null, $times = 1)
    {
        if (is_null($makeParameter)) {
            return self::makeFromArray([], $times);
        }

        if (is_array($makeParameter)) {
            return self::makeFromArray($makeParameter, $times);
        }

        if (is_callable($makeParameter)) {
            return self::makeFromCallable($makeParameter, $times);
        }

        throw new InvalidArgumentException("make only accepts null, an array or a callable as parameter");
    }

    public static function makeFromArray(array $data, $times = 1)
    {
        $factory = new static();
        $factory->times = $times;
        //$factory->mergeData($data);

        if ($times === 1) {
            $factory->mergeData($data);
        } else {
            for ($i = 0; $i < $times; $i++) {
                $factory->data[] = $data;
            }
        }

        return $factory;
    }

    public static function makeFromCallable(callable $fn, $times = 1)
    {
        $factory = new static();
        $factory->times = $times;

        if ($times === 1) {
            $returnedData = $fn($factory, $factory->getFaker());
            $factoryInjectedData = $factory->data;
            $tmpData = [];
            if (is_array($returnedData)) {
                $factory->mergeData($returnedData);
            }
        } else {
            $fn($factory, $factory->getFaker());
            $factoryInjectedData = $factory->data;
            $tmpData = [];
            for ($i = 0; $i < $times; $i++) {
                $tmpData[] = array_merge($factoryInjectedData, $fn($factory, $factory->getFaker()));
            }
            $factory->data = $tmpData;
        }

        return $factory;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        if ($this->times > 1) {
            throw new RuntimeException("Cannot call getEntity on a factory with {$this->times} records");
        }
        return $this->rootTable->newEntity($this->data, $this->getMarshallerOptions());
    }

    /**
     * @return EntityInterface
     */
    public function getEntities()
    {
        if ($this->times === 1) {
            throw new RuntimeException("Cannot call getEntities on a factory with 1 record");
        }
        return $this->rootTable->newEntities($this->data, $this->getMarshallerOptions());
    }

    /**
     * @return EntityInterface
     */
    private function persistOne()
    {
        // If the primary key is provided in the data, we do not
        // create the entity, but patch to the existing one
        $primaryKey = $this->rootTable->getPrimaryKey();
        if (
            isset($this->data[$primaryKey]) &&
            $entity = $this->rootTable->find()->where([$this->rootTable->aliasField($primaryKey) => $this->data[$primaryKey]])->first()
        ) {
            $entity = $this->rootTable->patchEntity($entity, $this->data, $this->getMarshallerOptions());
        } else {
            $entity = $this->rootTable->newEntity($this->data, $this->getMarshallerOptions());
        }

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

    private function persistMany()
    {
        $entities = $this->rootTable->newEntities($this->data, $this->getMarshallerOptions());
        return $this->rootTable->saveMany($entities, $this->getSaveOptions());
    }

    private function buildDataTemplate()
    {

    }

    public function persist()
    {
        if ($this->times === 1) {
            return $this->persistOne();
        } else {
            return $this->persistMany();
        }
    }

    /**
     * @param array $data
     * @return $this
     * @deprecated There is no need to directly use mergeData. This will become a private method in a future version.
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function getTable()
    {
        return $this->rootTable;
    }

    public function with(string $association, BaseFactory $factory)
    {
        $association = $this->getTable()->getAssociation($association);
        if ($association instanceof HasOne || $association instanceof BelongsTo) {
            return $this->withOne($association->getProperty(), $factory);
        }

        if ($association instanceof HasMany || $association instanceof BelongsToMany) {
            return $this->withMany($association->getProperty(), $factory);
        }

        throw new InvalidArgumentException("Unknown association type $association on table {$this->getTable()}");
    }

    /**
     * @param string      $association
     * @param BaseFactory $factory
     * @return $this
     */
    private function withOne(string $association, BaseFactory $factory): self
    {
        $this->data[$association] = $factory->getEntity()->toArray();
        $this->templateData[$association] = $factory;

        $this->associated[] = Inflector::camelize($association);

        foreach ($factory->getAssociated() as $associated) {
            $this->associated[] = Inflector::camelize($association) . "." . Inflector::camelize($associated);
        }

        return $this;
    }

    /**
     *  converts an array of entities to an array of arrays representing those entities
     */
    private function entitiesToArrays()
    {
        $arrays = [];
        foreach ($this->getEntities() as $entity) {
            /** @var $entity EntityInterface */
            $arrays[] = $entity->toArray();
        }
        return $arrays;
    }

    /**
     * @param string      $association
     * @param BaseFactory $factory
     * @return $this
     */
    private function withMany(string $association, BaseFactory $factory): self
    {
        $this->data[$association] = $factory->entitiesToArrays();
        $this->templateData[$association] = $factory;

        $this->associated[] = Inflector::camelize($association);

        foreach ($factory->getAssociated() as $associated) {
            $this->associated[] = Inflector::camelize($association) . "." . Inflector::camelize($associated);
        }

        return $this;
    }

    public function getAssociated()
    {
        return $this->associated;
    }

    public function mergeAssociated($data)
    {
        $this->associated = array_merge($this->associated, $data);

        return $this;
    }

    private function getSaveOptions()
    {
        return array_merge($this->saveOptions, [
            'associated' => $this->getAssociated()
        ]);
    }

    private function getMarshallerOptions()
    {
        return array_merge($this->marshallerOptions, [
            'associated' => $this->getAssociated()
        ]);
    }

    public function getTemplateData()
    {
        return $this->templateData;
    }

    /**
     * @return string
     */
    abstract protected function getRootTableRegistryName(): string;
}
