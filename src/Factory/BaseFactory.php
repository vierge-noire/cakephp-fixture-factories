<?php

namespace TestFixtureFactories\Factory;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Cake\Utility\Inflector;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use RuntimeException;
use TestFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use function array_merge;
use function count;
use function is_array;
use function is_callable;
use Cake\ORM\Table;

/**
 * Class BaseFactory
 *
 * @TODO    : add way to manage default values easily
 * @TODO    : throw exception when passing $times > 1 on hasOne association
 *
 * @package TestFixtureFactories\Factory
 */
abstract class BaseFactory
{
    //use LocatorAwareTrait;
    const WITH_ARRAY = 'WITH_ARRAY';
    const FROM_ARRAY = 'FROM_ARRAY';
    const FROM_CALLABLE = 'FROM_CALLABLE';
    const FROM_PATCH = 'FROM_PATCH';
    const FROM_DEFAULT = 'FROM_DEFAULT';
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
     * The number of records the factory should create
     *
     * @var int
     */
    private $times = 1;
    /**
     * @var array
     */
    private $templateData = [];
    /**
     * @var array
     */
    private $compiledTemplateData = [];
    /**
     * @var bool
     */
    private $isRootLevel = true;

    /**
     * BaseFactory constructor.
     *
     * @param array $data
     * @param array $options
     */
    protected function __construct()
    {
        $this->rootTable = FactoryTableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
    }

    /**
     * Table Registry the factory is bulding entities from
     * @return string
     */
    abstract protected function getRootTableRegistryName(): string;

    /**
     * @return void
     */
    abstract protected function setDefaultTemplate(): void;

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

        if ($makeParameter === false) {
            return null;
        }

        throw new InvalidArgumentException("make only accepts null, an array or a callable as the first parameter");
    }

    /**
     * @param array $data
     * @param int $times
     * @return static
     */
    public static function makeFromArray(array $data, $times = 1): BaseFactory
    {
        $factory = new static();
        $factory->times = $times;
        $factory->setDefaultTemplate();
        $factory->templateData[self::FROM_ARRAY] = $data;

        return $factory;
    }

    /**
     * @param callable $fn
     * @param int $times
     * @return static
     */
    public static function makeFromCallable(callable $fn, $times = 1): BaseFactory
    {
        $factory = new static();
        $factory->times = $times;
        $factory->setDefaultTemplate();

        // if the callable returns an array, add it the the templateData array, so it will be compiled
        $returnValue = $fn($factory, $factory->getFaker());
        if (is_array($returnValue)) {
            $factory->withArray($fn);
        }

        return $factory;
    }

    /**
     * @return Generator
     */
    protected function getFaker(): Generator
    {
        if (is_null(self::$faker)) {
            $faker = Factory::create();
            $faker->seed();
            self::$faker = $faker;
        }

        return self::$faker;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        if ($this->times > 1) {
            throw new RuntimeException("Cannot call getEntity on a factory with {$this->times} records");
        }
        $data = $this->toArray();

        return $this->rootTable->newEntity($data[0], $this->getMarshallerOptions());
    }

    /**
     * @return array
     */
    private function getMarshallerOptions(): array
    {
        return array_merge($this->marshallerOptions, [
            'associated' => $this->getAssociated()
        ]);
    }

    /**
     * @return array
     */
    public function getAssociated(): array
    {
        return $this->associated;
    }

    /**
     * @return array|EntityInterface[]
     */
    public function toEntities()
    {
        return $this->rootTable->newEntities($this->toArray(), $this->getMarshallerOptions());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->data = [];
        for ($i = 0; $i < $this->times; $i++) {
            $this->data[] = $this->compileTemplateData();
        }

        return $this->data;
    }

    /**
     * Populate the factored entity
     * @return array
     */
    private function compileTemplateData(): array
    {
        $this->compiledTemplateData = [];

        foreach ($this->templateData as $propertyName => $data) {
            $association = $this->getAssociationByPropertyName($propertyName);
            if ($association) {
                $dataIsFactory = $data instanceof BaseFactory;
                if ($dataIsFactory) {
                    /** @var BaseFactory $factory */
                    $factory = $data;
                    if ($association instanceof HasOne || $association instanceof BelongsTo) {
                        $this->compiledTemplateData[$propertyName] = $factory->toArray()[0];
                    } else {
                        $this->compiledTemplateData[$propertyName] = $factory->toArray();
                    }
                }
            }

            $isWithArray = $propertyName === self::WITH_ARRAY;
            if ($isWithArray) {
                $callable = $data;
                $array = $callable($this, $this->getFaker());
                $this->compiledTemplateData = array_merge($this->compiledTemplateData, $array);
            }

            $isFromArray = $propertyName === self::FROM_ARRAY;
            if ($isFromArray) {
                $array = $data;
                $this->compiledTemplateData = array_merge($this->compiledTemplateData, $array);
            }

            $isFromCallable = $propertyName === self::FROM_CALLABLE;
            if ($isFromCallable) {
                $callable = $data;
                $this->compiledTemplateData = array_merge($this->compiledTemplateData, $callable($this, $this->getFaker()));
            }

            $isFromDefault = $propertyName === self::FROM_DEFAULT;
            if ($isFromDefault) {
                $callable = $data;
                $this->compiledTemplateData = array_merge($this->compiledTemplateData, $callable($this->getFaker()));
            }

            $isFromPatch = $propertyName === self::FROM_PATCH;
            if ($isFromPatch) {
                $array = $data;
                $this->compiledTemplateData = array_merge($this->compiledTemplateData, $array);
            }
        }

        return $this->compiledTemplateData;
    }

    /**
     * @param string $propertyName
     * @return bool|Association
     */
    private function getAssociationByPropertyName(string $propertyName)
    {
        try {
            return $this->getTable()->getAssociation(Inflector::camelize($propertyName));
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * The table on which the factories are build
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->rootTable;
    }

    /**
     * @return array|EntityInterface|EntityInterface[]|\Cake\Datasource\ResultSetInterface|false|null
     * @throws \Exception
     */
    public function persist()
    {
        $this->data = [];
        for ($i = 0; $i < $this->times; $i++) {
            $this->data[] = $this->compileTemplateData();
        }
        if ($this->times === 1) {
            return $this->persistOne();
        } else {
            return $this->persistMany();
        }
    }


    /**
     * @return array|EntityInterface|null
     */
    private function persistOne()
    {
        // If the primary key is provided in the data, we do not
        // create the entity, but patch to the existing one
        $primaryKey = $this->rootTable->getPrimaryKey();
        if (
            isset($this->data[0][$primaryKey]) &&
            $entity = $this->rootTable->find()->where([$this->rootTable->aliasField($primaryKey) => $this->data[0][$primaryKey]])->first()
        ) {
            $entity = $this->rootTable->patchEntity($entity, $this->data[0], $this->getMarshallerOptions());
        } else {
            $entity = $this->rootTable->newEntity($this->data[0], $this->getMarshallerOptions());
        }

        $this->rootTable->saveOrFail($entity, $this->getSaveOptions());
        return $entity;
    }

    /**
     * @return array
     */
    private function getSaveOptions(): array
    {
        return array_merge($this->saveOptions, [
            'associated' => $this->getAssociated()
        ]);
    }

    /**
     * @return EntityInterface[]|\Cake\Datasource\ResultSetInterface|false
     * @throws \Exception
     */
    private function persistMany()
    {
        $entities = $this->rootTable->newEntities($this->data, $this->getMarshallerOptions());
        return $this->rootTable->saveMany($entities, $this->getSaveOptions());
    }

    /**
     * @param array $data
     * @return $this
     * @deprecated There is no need to directly use mergeData. This will become a private method in a future version.
     */
    public function mergeData(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Assigns the values of $data to the $keys of the entities generated
     * @param array $data
     * @return $this
     */
    public function patchData(array $data): self
    {
        if (isset($this->templateData[self::FROM_PATCH])) {
            $this->templateData[self::FROM_PATCH] = array_merge($this->templateData[self::FROM_PATCH], $data);
        } else {
            $this->templateData[self::FROM_PATCH] = $data;
        }

        return $this;
    }

    /**
     * Populate the entity factored
     * @param callable $fn
     * @return $this
     */
    protected function setDefaultData(callable $fn): self
    {
        $this->templateData['FROM_DEFAULT'] = $fn;
        return $this;
    }

    /**
     * @param string $associationName
     * @param BaseFactory $factory
     * @return $this
     */
    public function with(string $associationName, BaseFactory $factory): self
    {
        $association = $this->getTable()->getAssociation($associationName);
        if ($association instanceof HasOne || $association instanceof BelongsTo) {
            return $this->withOne(Inflector::underscore($association->getName()), $factory);
        }

        if ($association instanceof HasMany || $association instanceof BelongsToMany) {
            return $this->withMany(Inflector::underscore($association->getName()), $factory);
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
        //$this->data[$association] = $factory->getEntity()->toArray();
        $this->templateData[$association] = $factory;

        $this->associated[] = Inflector::camelize($association);

        foreach ($factory->getAssociated() as $associated) {
            $this->associated[] = Inflector::camelize($association) . "." . Inflector::camelize($associated);
        }

        return $this;
    }

    /**
     * @param string      $association
     * @param BaseFactory $factory
     * @return $this
     */
    private function withMany(string $association, BaseFactory $factory): self
    {
        //$this->data[$association] = $factory->entitiesToArrays();
        $this->templateData[$association] = $factory;

        $this->associated[] = Inflector::camelize($association);

        foreach ($factory->getAssociated() as $associated) {
            $this->associated[] = Inflector::camelize($association) . "." . Inflector::camelize($associated);
        }

        return $this;
    }

    /**
     * Unset a previously associated factory
     * Useful to unrule associations set in setDefaultTemplate
     * @param string $association
     * @return $this
     */
    public function without(string $association): self
    {
        unset($this->templateData[strtolower($association)]);
        return $this;
    }

    /**
     * @param callable $fn
     */
    private function withArray(callable $fn): void
    {
        $this->templateData[self::WITH_ARRAY] = $fn;
    }

    /**
     * @param $data
     * @return $this
     */
    public function mergeAssociated($data): self
    {
        $this->associated = array_merge($this->associated, $data);

        return $this;
    }


    /**
     * @return array|EntityInterface[]
     */
    public function getEntities()
    {
        if ($this->times === 1) {
            throw new RuntimeException("Cannot call getEntities on a factory with 1 record");
        }
        return $this->rootTable->newEntities($this->toArray(), $this->getMarshallerOptions());
    }
}
