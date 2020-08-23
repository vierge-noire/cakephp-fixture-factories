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
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use CakephpFixtureFactories\TestSuite\FixtureManager;
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
 * @package CakephpFixtureFactories\Factory
 */
abstract class BaseFactory
{
    /**
     * @var Generator
     */
    static private $faker = null;
    /**
     * @var bool
     */
    static protected $applyListenersAndBehaviors = false;
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
     * @var bool
     */
    protected $withModelEvents = false;
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
     */
    protected function __construct()
    {
        /**
         * Required to alias the connections before anything
         * in the test suite is launched. This solves the issue
         * with Factories produced in PHPUnit dataProviders
        **/
        (new FixtureManager())->aliasConnections();
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
     * @param int                     $times
     * @return self
     */
    public static function make($makeParameter = [], int $times = 1): self
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
            $factory->setTimes($times);
            $factory->setDefaultTemplate();
        }
        return $factory;
    }

    /**
     * @param array|callable|null|int $data
     * @param int                     $times
     * @return self
     */
    public static function makeWithModelEvents($makeParameter = [], $times = 1): self
    {
        $factory = self::make($makeParameter, $times);
        $factory->withModelEvents = true;
        return $factory;
    }

    /**
     * @param array $data
     * @param int $times
     * @return BaseFactory
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
     * @return BaseFactory
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
        $data = $this->toArray();

        if (count($data) > 1) {
            throw new RuntimeException("Cannot call getEntity on a factory with {$this->times} records");
        }

        return $this->getTable()->newEntity($data[0], $this->getMarshallerOptions());
    }

    /**
     * @return array
     */
    protected function getMarshallerOptions(): array
    {
        return array_merge($this->marshallerOptions, [
            'associated' => $this->getAssociationBuilder()->getAssociated()
        ]);
    }

    /**
     * @return array
     */
    public function getAssociated(): array
    {
        return $this->getAssociationBuilder()->getAssociated();
    }

    /**
     * @return array
     */
    protected function addAssociated(string $associationName, BaseFactory $factory)
    {
        $this->getAssociationBuilder()->collectAssociatedFactory($associationName, $factory);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        for ($i = 0; $i < $this->times; $i++) {
            $compiledData = $this->getDataCompiler()->getCompiledTemplateData();
            if (isset($compiledData[0])) {
                $data = array_merge($data, $compiledData);
            } else {
                $data[] = $compiledData;
            }
        }

        return $data;
    }

    /**
     * The table on which the factories are build
     * @return Table
     */
    public function getTable(): Table
    {
        if ($this->withModelEvents) {
            return TableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        } else {
            return FactoryTableRegistry::getTableLocator()->get($this->getRootTableRegistryName());
        }
    }

    /**
     * @return array|EntityInterface|EntityInterface[]|\Cake\Datasource\ResultSetInterface|false|null
     * @throws \Exception
     */
    public function persist()
    {
        $data = $this->toArray();

        try {
            if (count($data) === 1) {
                return $this->persistOne($data[0]);
            } else {
                return $this->persistMany($data);
            }
        } catch (\Exception $exception) {
            $factory = get_class($this);
            $message = $exception->getMessage();
            throw new PersistenceException("Error in Factory $factory.\n Message: $message \n");
        }
    }


    /**
     * @param $data
     * @return array|EntityInterface|null
     */
    protected function persistOne(array $data)
    {
        $entity = $this->getTable()->newEntity($data, $this->getMarshallerOptions());
        $this->getTable()->saveOrFail($entity, $this->getSaveOptions());
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
     * @param $data
     * @return EntityInterface[]|\Cake\Datasource\ResultSetInterface|false
     * @throws \Exception
     */
    protected function persistMany(array $data)
    {
        $entities = $this->getTable()->newEntities($data, $this->getMarshallerOptions());
        return $this->getTable()->saveMany($entities, $this->getSaveOptions());
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
     * A protected class dedicated to generating / collecting data for this factory
     * @return DataCompiler
     */
    protected function getDataCompiler(): DataCompiler
    {
        return $this->dataCompiler;
    }

    /**
     * A protected class dedicated to building / collecting associations for this factory
     * @return AssociationBuilder
     */
    protected function getAssociationBuilder(): AssociationBuilder
    {
        return $this->associationBuilder;
    }

    /**
     * Get the amount of entities generated by the factory
     * @return int
     */
    public function getTimes(): int
    {
        return $this->times;
    }

    /**
     * Set the amount of entities generated by the factory
     * @param int $times
     */
    public function setTimes(int $times): self
    {
        $this->times = $times;

        return $this;
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
        $this->getAssociationBuilder()->getAssociation($associationName);

        if (strpos($associationName, '.') === false && $data instanceof BaseFactory) {
            $factory = $data;
        } else {
            $factory = $this->getAssociationBuilder()->getAssociatedFactory($associationName, $data);
        }

        // Extract the first Association in the string
        $associationName = strtok($associationName, '.');

        // Remove the brackets in the association
        $associationName = $this->getAssociationBuilder()->removeBrackets($associationName);

        $this->getAssociationBuilder()->processToOneAssociation($associationName, $factory);

        $this->getDataCompiler()->collectAssociation($associationName, $factory);

        $this->addAssociated($associationName, $factory);

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
        $this->getAssociationBuilder()->dropAssociation($association);
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function mergeAssociated(array $data): self
    {
        $this->getAssociationBuilder()->setAssociated(
            array_merge(
                $this->getAssociationBuilder()->getAssociated(),
                $data
            )
        );

        return $this;
    }

    /**
     * Produce a set of entities from the present factory
     * @return array|EntityInterface[]
     */
    public function getEntities()
    {
        $data = $this->toArray();
        if (count($data) === 1) {
            throw new RuntimeException("Cannot call getEntities on a factory with 1 record");
        }
        return $this->getTable()->newEntities($data, $this->getMarshallerOptions());
    }
}
