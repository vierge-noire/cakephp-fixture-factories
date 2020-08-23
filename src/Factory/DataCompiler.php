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
use InvalidArgumentException;

class DataCompiler
{
    private $dataFromDefaultTemplate = [];
    private $dataFromInstantiation = [];
    private $dataFromPatch = [];
    private $dataFromAssociations = [];

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
        $this->dataFromAssociations[$associationName] = $factory;
    }

    /**
     * Scan for the data stored in the $association path provided and drop it
     * @param string $associationName
     * @return array
     */
    public function dropAssociation(string $associationName): void
    {
        unset($this->dataFromAssociations[$associationName]);
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

    public function compileEntity($injectedData)
    {
        $entity = [];
        // This order is very important!!!
        $this
            ->mergeWithDefaultTemplate($entity)
            ->mergeWithInjectedData($entity, $injectedData)
            ->mergeWithPatchedData($entity)
            ->mergeWithAssociatedData($entity);

        return $entity;
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
     * Merge with the data int the associations
     * @param array $compiledTemplateData
     */
    private function mergeWithAssociatedData(array &$compiledTemplateData): self
    {
        foreach ($this->dataFromAssociations as $propertyName => $data) {
            $association = $this->getAssociationByPropertyName($propertyName);
            if ($association && $data instanceof BaseFactory) {
                /** @var BaseFactory $dataFactory */
                $dataFactory = $data;
                $propertyName = $this->getMarshallerAssociationName($propertyName);
                if ($association instanceof HasOne || $association instanceof BelongsTo) {
                    // toOne associated data must be singular when saved
                    $propertyName = Inflector::singularize($propertyName);
                    $compiledTemplateData[$propertyName] = $dataFactory->toArray()[0];
                } else {
                    $compiledTemplateData[$propertyName] = $dataFactory->toArray();
                }
            }
        }
        return $this;
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
        $table = $this->getFactory()->getTable();
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
            return $this->getFactory()->getTable()->getAssociation(Inflector::camelize($propertyName));
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * @return BaseFactory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }
}