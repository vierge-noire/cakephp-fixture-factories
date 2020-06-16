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
    public function collectFromArray(array $data)
    {
        $this->dataFromInstantiation = $data;
    }

    /**
     * @param BaseFactory $factory
     * @param callable $fn
     */
    public function collectArrayFromCallable(callable $fn)
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
    public function collectFromPatch(array $data)
    {
        $this->dataFromPatch = array_merge($this->dataFromPatch, $data);
    }

    /**
     * @param callable $fn
     */
    public function collectFromDefaultTemplate(callable $fn)
    {
        $this->dataFromDefaultTemplate = $fn;
    }

    /**
     * @param string $associationName
     * @param BaseFactory $factory
     */
    public function collectAssociation(string $associationName, BaseFactory $factory)
    {
        $this->dataFromAssociations[
            $this->getMarshallerAssociationName($associationName)
        ] = $factory;
    }

    public function dropAssociation(string $associationName)
    {
        unset($this->dataFromAssociations[
            $this->getMarshallerAssociationName($associationName)
        ]);
    }

    /**
     * Populate the factored entity
     * @return array
     */
    public function getCompiledTemplateData(): array
    {
        $compiledTemplateData = [];

        // This order is very important!!!
        $this
            ->mergeWithDefaultTemplate($compiledTemplateData)
            ->mergeWithInjectedData($compiledTemplateData)
            ->mergeWithPatchedData($compiledTemplateData);

        foreach ($this->dataFromAssociations as $propertyName => $data) {
            $association = $this->getAssociationByPropertyName($propertyName);
            if ($association) {
                if ($data instanceof BaseFactory) {
                    /** @var BaseFactory $dataFactory */
                    $dataFactory = $data;
                    if ($association instanceof HasOne || $association instanceof BelongsTo) {
                        $compiledTemplateData[$propertyName] = $dataFactory->toArray()[0];
                    } else {
                        $compiledTemplateData[$propertyName] = $dataFactory->toArray();
                    }
                }
            }
        }

        return $compiledTemplateData;
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
     * @return $this
     */
    private function mergeWithInjectedData(array &$compiledTemplateData): self
    {
        $data = $this->dataFromInstantiation;
        if (is_callable($data)) {
            $array = $data(
                $this->getFactory(),
                $this->getFactory()->getFaker()
            );
            $compiledTemplateData = array_merge($compiledTemplateData, $array);
        } elseif (is_array($data)) {
            $compiledTemplateData = array_merge($compiledTemplateData, $data);
        }
        return $this;
    }

    /**
     * Step 3:
     * Merge with the data gathered by patching
     * Do not return this, as this is the last step
     * @param array $compiledTemplateData
     */
    private function mergeWithPatchedData(array &$compiledTemplateData)
    {
        $compiledTemplateData = array_merge($compiledTemplateData, $this->dataFromPatch);
    }

    /**
     * Returns lowercase underscored version of an association name
     * Throws an exception if the association name does not exist on the rootTable of the factory
     * @param string $associationName
     * @return string underscore_version of the input string
     * @throws \InvalidArgumentException
     */
    public function getMarshallerAssociationName(string $associationName): string
    {
        // Check that the association exists
        $this->getFactory()->getTable()->getAssociation($associationName);
        return Inflector::underscore($associationName);
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