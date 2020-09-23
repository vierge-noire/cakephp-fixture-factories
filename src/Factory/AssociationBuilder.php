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
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use CakephpFixtureFactories\Util;

class AssociationBuilder
{
    private $associated = [];

    /**
     * @var BaseFactory
     */
    private $factory;

    /**
     * AssociationBuilder constructor.
     * @param BaseFactory $factory
     */
    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Makes sure that a given association is well defined in the
     * builder's factory's table
     * @param string $associationName
     * @return Association
     */
    public function getAssociation(string $associationName): Association
    {
        $this->removeBrackets($associationName);

        try {
            $association = $this->getFactory()->getTable()->getAssociation($associationName);
        } catch (\Exception $e) {
            throw new AssociationBuilderException($e->getMessage());
        }
        if ($this->associationIsToOne($association) || $this->associationIsToMany($association)) {
            return $association;
        } else {
            throw new AssociationBuilderException("Unknown association type $association on table {$this->getFactory()->getTable()}");
        }
    }

    /**
     * Collect an associated factory to the BaseFactory
     * @param string $associationName
     * @param BaseFactory $factory
     */
    public function collectAssociatedFactory(string $associationName, BaseFactory  $factory)
    {
        $associations = $this->getAssociated();

        if (!in_array($associationName, $associations)) {
            $associations[$associationName] = $factory->getMarshallerOptions();
        }

        $this->setAssociated($associations);
    }

    public function processToOneAssociation(string $associationName, BaseFactory $associationFactory)
    {
        $this->validateToOneAssociation($associationName, $associationFactory);
        $this->removeAssociatedAssociationForToOneFactory($associationName, $associationFactory);
    }

    /**
     * HasOne and BelongsTo association cannot be multiple
     * @param string $associationName
     * @param BaseFactory $associationFactory
     * @return bool
     */
    public function validateToOneAssociation(string $associationName, BaseFactory $associationFactory): bool
    {
        if ($this->associationIsToOne($this->getAssociation($associationName)) && $associationFactory->getTimes() > 1) {
            throw new AssociationBuilderException(
                "Association $associationName on " . $this->getFactory()->getTable()->getEntityClass() . " cannot be multiple");
        }
        return true;
    }

    public function removeAssociatedAssociationForToOneFactory(string $associationName, BaseFactory $associatedFactory)
    {
        if ($this->associationIsToOne($this->getAssociation($associationName))) {
            return;
        }

        $thisFactoryRegistryName = $this->getFactory()->getTable()->getRegistryAlias();
        $associatedFactoryTable = $associatedFactory->getTable();

        $associatedAssociationName = Inflector::singularize($thisFactoryRegistryName);

        if ($associatedFactoryTable->hasAssociation($associatedAssociationName)) {
            $associatedFactory->without($associatedAssociationName);
        }
    }

    /**
     * Get the factory for the association
     * @param string $associationName
     * @param array $data
     * @return BaseFactory
     */
    public function getAssociatedFactory(string $associationName, $data = []): BaseFactory
    {
        $associations = explode('.', $associationName);
        $firstAssociation = array_shift($associations);

        $times = $this->getTimeBetweenBrackets($firstAssociation);
        $this->removeBrackets($firstAssociation);

        $table = $this->getFactory()->getTable()->getAssociation($firstAssociation)->getClassName() ?? $this->getFactory()->getTable()->getAssociation($firstAssociation)->getName();

        if (!empty($associations)) {
            $factory = $this->getFactoryFromTableName($table);
            $factory->with(implode('.', $associations), $data);
        } else {
            $factory = $this->getFactoryFromTableName($table, $data);
        }
        if ($times) {
            $factory->setTimes($times);
        }
        return $factory;
    }

    /**
     * Get a factory from a table name
     * @param string $modelName
     * @param array $data
     * @return BaseFactory
     */
    public function getFactoryFromTableName(string $modelName, $data = []): BaseFactory
    {
        $factoryName = Util::getFactoryClassFromModelName($modelName);
        try {
            return $factoryName::make($data);
        } catch (\Error $e) {
            throw new AssociationBuilderException($e->getMessage());
        }
    }

    /**
     * Remove the brackets and there content in a n 'Association1[i].Association2[j]' formatted string
     * @param string $string
     * @return string
     */
    public function removeBrackets(string &$string): string
    {
        return $string = preg_replace("/\[[^]]+\]/","", $string);
    }

    /**
     * Return the integer i between brackets in an 'Association[i]' formatted string
     * @param string $string
     * @return int|null
     */
    public function getTimeBetweenBrackets(string $string)
    {
        preg_match_all("/\[([^\]]*)\]/", $string, $matches);
        $res = $matches[1];
        if (empty($res)) {
            return null;
        } elseif (count($res) === 1 && !empty($res[0])) {
            return (int) $res[0];
        } else {
            throw new AssociationBuilderException("Error parsing $string.");
        }
    }

    /**
     * @return BaseFactory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }

    /**
     * @param Association $association
     * @return bool
     */
    public function associationIsToOne(Association $association): bool
    {
        return ($association instanceof HasOne || $association instanceof BelongsTo);
    }

    /**
     * @param Association $association
     * @return bool
     */
    public function associationIsToMany(Association $association): bool
    {
        return ($association instanceof HasMany || $association instanceof BelongsToMany);
    }

    /**
     * Scan for all associations starting by the $association path provided and drop them
     * @param string $associationName
     * @return void
     */
    public function dropAssociation(string $associationName)
    {
        $this->setAssociated(
            Hash::remove(
                $this->getAssociated(),
                $associationName
            )
        );
    }

    /**
     * @return array
     */
    public function getAssociated(): array
    {
        return $this->associated;
    }

    /**
     * @param array $associated
     */
    public function setAssociated(array $associated)
    {
        $this->associated = $associated;
    }
}