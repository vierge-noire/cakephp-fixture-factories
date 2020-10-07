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

use Cake\ORM\Association;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\HasOne;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Util;
use InvalidArgumentException;

class DataCompiler
{
    private $dataFromDefaultTemplate = [];
    private $dataFromInstantiation = [];
    private $dataFromPatch = [];
    private $dataFromAssociations = [];
    private $dataFromDefaultAssociations = [];
    private $primaryKeyOffset = true;

    static private $inPersistMode = false;

    /**
     * @var BaseFactory
     */
    private $factory;

    /**
     * DataCompiler constructor.
     * @param BaseFactory $factory
     */
    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
        $this->primaryKeyOffset = !Util::isRunningOnPostgresql($factory);
    }

    /**
     * Data passed in the instantiation by array
     * @param array $data
     */
    public function collectFromArray(array $data): void
    {
        $this->dataFromInstantiation = $data;
    }

    /**
     * @param BaseFactory $factory
     * @param callable $fn
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
     * @param array $data
     */
    public function collectFromPatch(array $data): void
    {
        $this->dataFromPatch = array_merge($this->dataFromPatch, $data);
    }

    /**
     * @param callable $fn
     */
    public function collectFromDefaultTemplate(callable $fn): void
    {
        $this->dataFromDefaultTemplate = $fn;
    }

    /**
     * @param string $associationName
     * @param BaseFactory $factory
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
     * @param string $associationName
     * @return array
     */
    public function dropAssociation(string $associationName): void
    {
        unset($this->dataFromAssociations[$associationName]);
        unset($this->dataFromDefaultAssociations[$associationName]);
    }

    /**
     * Populate the factored entity
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
     * @param      $injectedData
     *
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
     * @param array $compiledTemplateData
     * @return $this
     */
    private function mergeWithDefaultTemplate(array &$compiledTemplateData): self
    {
        if (!empty($compiledTemplateData)) {
            throw new FixtureFactoryException('The initial array before merging with the default template should be empty');
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
     * @param array $compiledTemplateData
     * @param array|callable $injectedData
     * @return $this
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
     * @param array $compiledTemplateData
     */
    private function mergeWithPatchedData(array &$compiledTemplateData): self
    {
        $compiledTemplateData = array_merge($compiledTemplateData, $this->dataFromPatch);
        return $this;
    }

    /**
     * Step 4:
     * Merge with the data from the associations
     * @param array $compiledTemplateData
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
     * @param $compiledTemplateData
     * @param $associationName
     * @param $data
     */
    private function mergeWithToOne(array &$compiledTemplateData, string $associationName, array $data)
    {
        $count                                  = count($data);
        $associationName                        = Inflector::singularize($associationName);
        /** @var BaseFactory $factory */
        $factory = $data[$count - 1];
        $compiledTemplateData[$associationName] = $factory->getEntity()->toArray();
    }

    /**
     * @param $compiledTemplateData
     * @param $associationName
     * @param $data
     */
    private function mergeWithToMany(array &$compiledTemplateData, string $associationName, array $data)
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
     * @param BaseFactory $factory
     *
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
     */
    public function collectAssociationsFromDefaultTemplate()
    {
        $this->dataFromDefaultAssociations = $this->dataFromAssociations;
        $this->dataFromAssociations        = [];
    }

    /**
     * Returns the property name of the association. This can be dot separated for deep associations
     * Throws an exception if the association name does not exist on the rootTable of the factory
     * @param string $associationName
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
     * @param string $propertyName
     * @return bool|Association
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
     * @param array $data
     *
     * @return array
     */
    public function setPrimaryKey(array $data): array
    {
        if (!$this->isInPersistMode() || !$this->primaryKeyOffset) {
            return $data;
        }
        if (isset($data[0])) {
            $data[0] = $this->setPrimaryKey($data[0]);
        } else {
            $data[$this->getRootTablePrimaryKey()] = $data[$this->getRootTablePrimaryKey()] ?? $this->getPrimaryKeyOffset();
            $this->primaryKeyOffset = null;
        }
        return $data;
    }

    public function getPrimaryKeyOffset(): int
    {
        return ($this->primaryKeyOffset === true) ? $this->generateRandomPrimaryKey() : (int) $this->primaryKeyOffset;
    }

    public function getRootTablePrimaryKey(): string
    {
        return $this->getFactory()->getRootTableRegistry()->getPrimaryKey();
    }

    /**
     * Credits to Faker
     * https://github.com/fzaninotto/Faker/blob/master/src/Faker/ORM/CakePHP/ColumnTypeGuesser.php
     * @return int
     */
    public function generateRandomPrimaryKey(): int
    {
        switch ($this->getFactory()->getRootTableRegistry()->getSchema()->getColumnType($this->getRootTablePrimaryKey())) {
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
     * @return BaseFactory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }

    /**
     * @param bool|int $primaryKeyOffset
     */
    public function setPrimaryKeyOffset($primaryKeyOffset): void
    {
        $this->primaryKeyOffset = $primaryKeyOffset;
    }

    /**
     * @return bool
     */
    public function isInPersistMode(): bool
    {
        return self::$inPersistMode;
    }

    public function startPersistMode(): void
    {
        self::$inPersistMode = true;
    }

    public function endPersistMode(): void
    {
        self::$inPersistMode = false;
    }
}