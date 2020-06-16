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

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use RuntimeException;
use function array_merge;
use function is_array;
use function is_callable;

/**
 * Class BaseFactory
 *
 * @TODO    : throw exception when passing $times > 1 on hasOne association
 *
 * @package CakephpFixtureFactories\Factory
 */
abstract class BaseFactory
{
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
     * The data compiler gathers the data from the
     * default template, the injection and patched data
     * and compiles it to produce the data feeding the
     * entities of the Factory
     *
     * @var DataCompiler
     */
    private $dataCompiler;
    /**
     * Helper to check and build data in associations
     * @var AssociationBuilder
     */
    private $associationBuilder;

    /**
     * BaseFactory constructor.
     *
     * @param array $data
     * @param array $options
     */
    protected function __construct()
    {
        $this->rootTable = FactoryTableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        $this->dataCompiler = new DataCompiler($this);
        $this->associationBuilder = new AssociationBuilder($this);
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
     * @param array|callable|null|int $data
     * @param array               $options
     * @return static
     */
    public static function make($makeParameter = [], $times = 1): BaseFactory
    {
        if (is_numeric($makeParameter)) {
            $factory = self::makeFromArray();
            $times = $makeParameter;
        } elseif (is_null($makeParameter)) {
            $factory = self::makeFromArray();
        } elseif (is_array($makeParameter)) {
            $factory = self::makeFromArray($makeParameter);
        } elseif (is_callable($makeParameter)) {
            $factory = self::makeFromCallable($makeParameter);
        } elseif ($makeParameter === false) {
            $factory = null;
        } else {
            throw new InvalidArgumentException("make only accepts null, an array or a callable as the first parameter");
        }

        if ($factory) {
            $factory->times = $times;
            $factory->setDefaultTemplate();
        }
        return $factory;
    }

    /**
     * @param array $data
     * @param int $times
     * @return static
     */
    private static function makeFromArray(array $data = []): BaseFactory
    {
        $factory = new static();
        $factory->getDataCompiler()->collectFromArray($data);
        return $factory;
    }

    /**
     * @param callable $fn
     * @param int $times
     * @return static
     */
    private static function makeFromCallable(callable $fn): BaseFactory
    {
        $factory = new static();
        $factory->getDataCompiler()->collectArrayFromCallable($fn);
        return $factory;
    }

    /**
     * @return Generator
     */
    public function getFaker(): Generator
    {
        if (is_null(self::$faker)) {
            $faker = Factory::create();
            $faker->seed(1234);
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
            $this->data[] = $this->getDataCompiler()->getCompiledTemplateData();
        }

        return $this->data;
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
            $this->data[] = $this->getDataCompiler()->getCompiledTemplateData();
        }
        try {
            if ($this->times === 1) {
                return $this->persistOne();
            } else {
                return $this->persistMany();
            }
        } catch (\Exception $exception) {
            $factory = get_class($this);
            $message = $exception->getMessage();
            throw new PersistenceException("Error in Factory $factory.\n Message: $message \n");
        }
    }


    /**
     * @return array|EntityInterface|null
     */
    private function persistOne()
    {
        $entity = $this->rootTable->newEntity($this->data[0], $this->getMarshallerOptions());
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
     * Assigns the values of $data to the $keys of the entities generated
     * @param array $data
     * @return $this
     */
    public function patchData(array $data): self
    {
        $this->getDataCompiler()->collectFromPatch($data);
        return $this;
    }

    /**
     * @return DataCompiler
     */
    protected function getDataCompiler(): DataCompiler
    {
        return $this->dataCompiler;
    }

    /**
     * @return AssociationBuilder
     */
    protected function getAssociationBuilder(): AssociationBuilder
    {
        return $this->associationBuilder;
    }

    /**
     * @return int
     */
    public function getTimes(): int
    {
        return $this->times;
    }

    /**
     * @param int $times
     */
    public function setTimes(int $times): void
    {
        $this->times = $times;
    }

    /**
     * Populate the entity factored
     * @param callable $fn
     * @return $this
     */
    protected function setDefaultData(callable $fn): self
    {
        $this->getDataCompiler()->collectFromDefaultTemplate($fn);
        return $this;
    }

    /**
     * Add associated entities to the fixtures generated by the factory
     * The associated name can be of several level, dot separated
     * The data can be an array, an integer, a method or a factory
     * @param string $associationName
     * @param $data
     * @return $this
     */
    public function with(string $associationName, $data = []): self
    {
        $this->getAssociationBuilder()->checkAssociation($associationName);

        if ($data instanceof BaseFactory) {
            $factory = $data;
        } else {
            $factory = $this->getAssociationBuilder()->getAssociatedFactory($associationName, $data);
        }

        $associationName = strtok($associationName, '.');

        $this->getAssociationBuilder()->validateToOneAssociation($associationName, $factory);

        $this->getDataCompiler()->collectAssociation($associationName, $factory);

        $this->associated[] = $associationName;

        foreach ($factory->getAssociated() as $associated) {
            $this->associated[] = $associationName . "." . Inflector::camelize($associated);
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
        $this->getDataCompiler()->dropAssociation($association);
        return $this;
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
