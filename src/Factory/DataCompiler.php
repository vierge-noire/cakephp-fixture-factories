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

use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\HasOne;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Util;
use InvalidArgumentException;

class DataCompiler
{
    private $dataFromDefaultTemplate = [];
    private $dataFromInstantiation = [];
    private $dataFromPatch = [];
    private $dataFromAssociations = [];
    private $dataFromDefaultAssociations = [];
    private $primaryKeyOffset = [];

    private static $inPersistMode = false;

    /**
     * @var \CakephpFixtureFactories\Factory\BaseFactory
     */
    private $factory;

    /**
     * DataCompiler constructor.
     *
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Master factory
     */
    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Data passed in the instantiation by array
     *
     * @param array $data Injected data
     * @return void
     */
    public function collectFromArray(array $data): void
    {
        $this->dataFromInstantiation = $data;
    }

    /**
     * Data passed in the instantiation by callable
     *
     * @param callable $fn Injected callable
     * @return void
     */
    public function collectArrayFromCallable(callable $fn): void
    {
        // if the callable returns an array, add it the the templateData array, so it will be compiled
        $returnValue = $fn($this->getFactory(), $this->getFactory()->getFaker());
        if (is_array($returnValue)) {
            $this->dataFromInstantiation = $fn;
        }
    }

    /**
     * @param array $data Collected data
     * @return void
     */
    public function collectFromPatch(array $data): void
    {
        $this->dataFromPatch = array_merge($this->dataFromPatch, $data);
    }

    /**
     * @param callable $fn Collected data from default template
     * @return void
     */
    public function collectFromDefaultTemplate(callable $fn): void
    {
        $this->dataFromDefaultTemplate = $fn;
    }

    /**
     * @param string $associationName Association name
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Collected factory
     * @return void
     */
    public function collectAssociation(string $associationName, BaseFactory $factory): void
    {
        if (isset($this->dataFromAssociations[$associationName])) {
            $this->dataFromAssociations[$associationName][] = $factory;
        } else {
            $this->dataFromAssociations[$associationName] = [$factory];
        }
    }

    /**
     * Scan for the data stored in the $association path provided and drop it
     *
     * @param string $associationName Association name
     * @return void
     */
    public function dropAssociation(string $associationName): void
    {
        unset($this->dataFromAssociations[$associationName]);
        unset($this->dataFromDefaultAssociations[$associationName]);
    }

    /**
     * Populate the factored entity
     *
     * @return array
     */
    public function getCompiledTemplateData(): array
    {
        if (is_array($this->dataFromInstantiation) && isset($this->dataFromInstantiation[0])) {
            $compiledTemplateData = [];
            foreach ($this->dataFromInstantiation as $entity) {
                $compiledTemplateData[] = $this->compileEntity($entity);
            }
        } else {
            $compiledTemplateData = $this->compileEntity($this->dataFromInstantiation);
        }

        return $compiledTemplateData;
    }

    /**
     * @param array|callable      $injectedData Data from the injection
     * @return array
     */
    public function compileEntity($injectedData): array
    {
        $entity = [];
        // This order is very important!!!
        $this
            ->mergeWithDefaultTemplate($entity)
            ->mergeWithInjectedData($entity, $injectedData)
            ->mergeWithPatchedData($entity)
            ->mergeWithAssociatedData($entity);

        return $this->setPrimaryKey($entity);
    }

    /**
     * Step 1: merge the default template data
     *
     * @param array $compiledTemplateData Array of data produced by the factory
     * @return self
     */
    private function mergeWithDefaultTemplate(array &$compiledTemplateData): self
    {
        if (!empty($compiledTemplateData)) {
            throw new FixtureFactoryException(
                'The initial array before merging with the default template should be empty'
            );
        }
        $data = $this->dataFromDefaultTemplate;
        if (is_array($data)) {
            $compiledTemplateData = array_merge($compiledTemplateData, $data);
        } elseif (is_callable($data)) {
            $compiledTemplateData = array_merge($compiledTemplateData, $data($this->getFactory()->getFaker()));
        }

        return $this;
    }

    /**
     * Step 2:
     * Merge with the data injected during the instantiation of the Factory
     *
     * @param array $compiledTemplateData Array of data produced by the factory
     * @param array|callable $injectedData Data from the instanciation
     * @return self
     */
    private function mergeWithInjectedData(array &$compiledTemplateData, $injectedData): self
    {
        if (is_callable($injectedData)) {
            $array = $injectedData(
                $this->getFactory(),
                $this->getFactory()->getFaker()
            );
            $compiledTemplateData = array_merge($compiledTemplateData, $array);
        } elseif (is_array($injectedData)) {
            $compiledTemplateData = array_merge($compiledTemplateData, $injectedData);
        }

        return $this;
    }

    /**
     * Step 3:
     * Merge with the data gathered by patching
     * Do not return this, as this is the last step
     *
     * @param array $compiledTemplateData Array of data produced by the factory
     * @return self
     */
    private function mergeWithPatchedData(array &$compiledTemplateData): self
    {
        $compiledTemplateData = array_merge($compiledTemplateData, $this->dataFromPatch);

        return $this;
    }

    /**
     * Step 4:
     * Merge with the data from the associations
     *
     * @param array $compiledTemplateData Array of data produced by the factory
     * @return self
     */
    private function mergeWithAssociatedData(array &$compiledTemplateData): self
    {
        // Overwrite the default associations if these are found in the associations
        $associatedData = array_merge($this->dataFromDefaultAssociations, $this->dataFromAssociations);

        foreach ($associatedData as $propertyName => $data) {
            $association = $this->getAssociationByPropertyName($propertyName);
            $propertyName = $this->getMarshallerAssociationName($propertyName);
            if ($association instanceof HasOne || $association instanceof BelongsTo) {
                // toOne associated data must be singular when saved
                $this->mergeWithToOne($compiledTemplateData, $propertyName, $data);
            } else {
                $this->mergeWithToMany($compiledTemplateData, $propertyName, $data);
            }
        }

        return $this;
    }

    /**
     * There might be several data feeding a toOne relation
     * One reason can be the default template value.
     * Here the latest inserted record is taken
     *
     * @param array $compiledTemplateData Array of data produced by the factory
     * @param string $associationName Association
     * @param array $data Data to inject
     * @return void
     */
    private function mergeWithToOne(array &$compiledTemplateData, string $associationName, array $data): void
    {
        $count = count($data);
        $associationName = Inflector::singularize($associationName);
        /** @var \CakephpFixtureFactories\Factory\BaseFactory $factory */
        $factory = $data[$count - 1];
        $compiledTemplateData[$associationName] = $factory->getEntity()->toArray();
    }

    /**
     * @param array $compiledTemplateData Array of data produced by the factory
     * @param string $associationName Association
     * @param array $data Data to inject
     * @return void
     */
    private function mergeWithToMany(array &$compiledTemplateData, string $associationName, array $data): void
    {
        $associationData = $compiledTemplateData[$associationName] ?? null;
        foreach ($data as $factory) {
            if ($associationData) {
                $associationData = array_merge($associationData, $this->getManyEntities($factory));
            } else {
                $associationData = $this->getManyEntities($factory);
            }
        }
        $compiledTemplateData[$associationName] = $associationData;
    }

    /**
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Factory
     * @return array
     */
    private function getManyEntities(BaseFactory $factory): array
    {
        $result = [];
        foreach ($factory->getEntities() as $entity) {
            $result[] = $entity->toArray();
        }

        return $result;
    }

    /**
     * Used in the Factory make in order to distinguish default associations
     * from conscious associations
     *
     * @return void
     */
    public function collectAssociationsFromDefaultTemplate(): void
    {
        $this->dataFromDefaultAssociations = $this->dataFromAssociations;
        $this->dataFromAssociations = [];
    }

    /**
     * Returns the property name of the association. This can be dot separated for deep associations
     * Throws an exception if the association name does not exist on the rootTable of the factory
     *
     * @param string $associationName Association
     * @return string underscore_version of the input string
     * @throws \InvalidArgumentException
     */
    public function getMarshallerAssociationName(string $associationName): string
    {
        $result = [];
        $cast = explode('.', $associationName);
        $table = $this->getFactory()->getRootTableRegistry();
        foreach ($cast as $i => $ass) {
            $association = $table->getAssociation($ass);
            $result[] = $association->getProperty();
            $table = $association->getTarget();
        }

        return implode('.', $result);
    }

    /**
     * @param string $propertyName Property
     * @return bool|\Cake\ORM\Association
     */
    public function getAssociationByPropertyName(string $propertyName)
    {
        try {
            return $this->getFactory()->getRootTableRegistry()->getAssociation(Inflector::camelize($propertyName));
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * @param array $data Set of primary keys
     * @return array
     */
    public function setPrimaryKey(array $data): array
    {
        // A set of primary keys is produced if in persistence mode, and if a first set was not produced yet
        if (!$this->isInPersistMode() || !is_array($this->primaryKeyOffset)) {
            return $data;
        }

        // If we have an array of multiple entities, set only for the first one
        if (isset($data[0])) {
            $data[0] = $this->setPrimaryKey($data[0]);
        } else {
            $data = array_merge(
                $this->createPrimaryKeyOffset(),
                $data
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    public function createPrimaryKeyOffset(): array
    {
        if (!is_array($this->primaryKeyOffset)) {
            throw new PersistenceException('A set of primary keys was already created');
        }
        $res = empty($this->primaryKeyOffset) ? $this->generateArrayOfRandomPrimaryKeys() : $this->primaryKeyOffset;

        $this->updatePostgresSequence($res);

        // Set to null, this factory will never generate a primaryKeyOffset again
        $this->primaryKeyOffset = null;

        return $res;
    }

    /**
     * Get the primary key, or set of composite primary keys
     *
     * @return string|string[]
     */
    public function getRootTablePrimaryKey()
    {
        return $this->getFactory()->getRootTableRegistry()->getPrimaryKey();
    }

    /**
     * @return array
     */
    public function generateArrayOfRandomPrimaryKeys(): array
    {
        $primaryKeys = (array)$this->getRootTablePrimaryKey();
        $res = [];
        foreach ($primaryKeys as $pk) {
            $res[$pk] = $this->generateRandomPrimaryKey(
                $this->getFactory()->getRootTableRegistry()->getSchema()->getColumnType($pk)
            );
        }

        return $res;
    }

    /**
     * Credits to Faker
     * https://github.com/fzaninotto/Faker/blob/master/src/Faker/ORM/CakePHP/ColumnTypeGuesser.php
     *
     * @param string $columnType Column type
     * @return int|string
     */
    public function generateRandomPrimaryKey(string $columnType)
    {
        switch ($columnType) {
            case 'uuid':
                $res = $this->getFactory()->getFaker()->uuid;
                break;
            case 'biginteger':
                $res = mt_rand(0, intval('9223372036854775807'));
                break;
            case 'integer':
            default:
                $res = mt_rand(0, intval('2147483647'));
                break;
        }

        return $res;
    }

    /**
     * @return \CakephpFixtureFactories\Factory\BaseFactory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }

    /**
     * @param int|string|array $primaryKeyOffset Name of the primary key
     * @return void
     */
    public function setPrimaryKeyOffset($primaryKeyOffset): void
    {
        if (is_int($primaryKeyOffset) || is_string($primaryKeyOffset)) {
            $this->primaryKeyOffset = [
                $this->getRootTablePrimaryKey() => $primaryKeyOffset,
            ];
        } elseif (is_array($primaryKeyOffset)) {
            $this->primaryKeyOffset = $primaryKeyOffset;
        } else {
            throw new FixtureFactoryException(
                "$primaryKeyOffset must be an integer, a string or an array of format ['primaryKey1' => value, ...]"
            );
        }
    }

    /**
     * @param array $primaryKeys Set of primary keys
     * @return void
     */
    private function updatePostgresSequence(array $primaryKeys): void
    {
        if (Util::isRunningOnPostgresql($this->getFactory())) {
            $tableName = $this->getFactory()->getRootTableRegistry()->getTable();

            foreach ($primaryKeys as $pk => $offset) {
                $this->getFactory()->getRootTableRegistry()->getConnection()->execute(
                    "SELECT setval('$tableName" . "_$pk" . "_seq', $offset);"
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function isInPersistMode(): bool
    {
        return self::$inPersistMode;
    }

    /**
     * @return void
     */
    public function startPersistMode(): void
    {
        self::$inPersistMode = true;
    }

    /**
     * @return void
     */
    public function endPersistMode(): void
    {
        self::$inPersistMode = false;
    }
}
