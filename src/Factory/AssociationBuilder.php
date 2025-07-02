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
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use Exception;
use Throwable;

/**
 * Class AssociationBuilder
 *
 * @internal
 */
class AssociationBuilder
{
    use FactoryAwareTrait {
        getFactory as getFactoryInstance;
    }

    /**
     * @var array<\CakephpFixtureFactories\Factory\BaseFactory>
     */
    private array $associations = [];

    /**
     * @var array
     */
    private array $manualAssociations = [];

    /**
     * @var \CakephpFixtureFactories\Factory\BaseFactory
     */
    private BaseFactory $factory;

    /**
     * AssociationBuilder constructor.
     *
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Associated factory
     */
    public function __construct(BaseFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Makes sure that a given association is well defined in the
     * builder's factory's table
     *
     * @param string $associationName Name of the association
     * @return \Cake\ORM\Association
     */
    public function getAssociation(string $associationName): Association
    {
        $associationName = $this->removeBrackets($associationName);

        try {
            $association = $this->getTable()->getAssociation($associationName);
        } catch (Exception $e) {
            throw new AssociationBuilderException($e->getMessage());
        }
        if ($this->associationIsToOne($association) || $this->associationIsToMany($association)) {
            return $association;
        } else {
            $associationType = get_class($association);
            throw new AssociationBuilderException(
                "Unknown association type $associationType on table {$this->getTable()->getAlias()}",
            );
        }
    }

    /**
     * @param string      $associationName Name of the association
     * @param \CakephpFixtureFactories\Factory\BaseFactory $associationFactory Factory
     * @return bool
     */
    public function processToOneAssociation(string $associationName, BaseFactory $associationFactory): bool
    {
        $this->validateToOneAssociation($associationName, $associationFactory);
        $this->removeAssociationForToOneFactory($associationName, $associationFactory);

        return $this->associationIsToOne($this->getAssociation($associationName));
    }

    /**
     * HasOne and BelongsTo association cannot be multiple
     *
     * @param string $associationName Name of the association
     * @param \CakephpFixtureFactories\Factory\BaseFactory $associationFactory Factory
     * @return bool
     */
    public function validateToOneAssociation(string $associationName, BaseFactory $associationFactory): bool
    {
        if ($this->associationIsToOne($this->getAssociation($associationName)) && $associationFactory->getTimes() > 1) {
            throw new AssociationBuilderException(
                "Association $associationName on " . $this->getTable()->getEntityClass() . ' cannot be multiple',
            );
        }

        return true;
    }

    /**
     * @param string      $associationName Association name
     * @param \CakephpFixtureFactories\Factory\BaseFactory $associatedFactory Factory
     * @return void
     */
    public function removeAssociationForToOneFactory(string $associationName, BaseFactory $associatedFactory): void
    {
        if ($this->associationIsToMany($this->getAssociation($associationName))) {
            $associatedAssociationName = Inflector::singularize($this->getTable()->getRegistryAlias());
            if ($associatedFactory->getTable()->hasAssociation($associatedAssociationName)) {
                $associatedFactory->without($associatedAssociationName);
            }
        }
    }

    /**
     * Get the factory for the association
     *
     * @param string $associationName Association name
     * @param mixed $data Injected data
     * @return \CakephpFixtureFactories\Factory\BaseFactory
     */
    public function getAssociatedFactory(
        string $associationName,
        mixed $data = [],
    ): BaseFactory {
        $associations = explode('.', $associationName);
        $firstAssociation = array_shift($associations);

        $times = $this->getTimeBetweenBrackets($firstAssociation);
        $firstAssociation = $this->removeBrackets($firstAssociation);

        $table = $this->getTable()->getAssociation($firstAssociation)->getClassName();

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
     *
     * @param string $modelName Model Name
     * @param mixed $data Injected data
     * @return \CakephpFixtureFactories\Factory\BaseFactory
     */
    public function getFactoryFromTableName(string $modelName, mixed $data = []): BaseFactory
    {
        try {
            return $this->getFactoryInstance($modelName, $data);
        } catch (Throwable $e) {
            throw new AssociationBuilderException($e->getMessage());
        }
    }

    /**
     * Remove the brackets and their content in an 'Association1[i].Association2[j]' formatted string
     *
     * @param string $string String
     * @return string|null
     */
    public function removeBrackets(string $string): ?string
    {
        return preg_replace("/\[[^]]+\]/", '', $string);
    }

    /**
     * Return the integer i between brackets in an 'Association[i]' formatted string
     *
     * @param string $string String
     * @return int|null
     */
    public function getTimeBetweenBrackets(string $string): ?int
    {
        preg_match_all("/\[([^\]]*)\]/", $string, $matches);
        $res = $matches[1];
        if (empty($res)) {
            return null;
        } elseif (count($res) === 1 && !empty($res[0])) {
            return (int)$res[0];
        } else {
            throw new AssociationBuilderException("Error parsing $string.");
        }
    }

    /**
     * @return \CakephpFixtureFactories\Factory\BaseFactory Factory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }

    /**
     * @param \Cake\ORM\Association $association Association
     * @return bool
     */
    public function associationIsToOne(Association $association): bool
    {
        return $association instanceof HasOne || $association instanceof BelongsTo;
    }

    /**
     * @param \Cake\ORM\Association $association Association
     * @return bool
     */
    public function associationIsToMany(Association $association): bool
    {
        return $association instanceof HasMany || $association instanceof BelongsToMany;
    }

    /**
     * Scan for all associations starting by the $association path provided and drop them
     *
     * @param string $associationName Association name
     * @return void
     */
    public function dropAssociation(string $associationName): void
    {
        $explode = explode('.', $associationName);
        $baseAssociationName = array_shift($explode);
        if (!isset($this->associations[$baseAssociationName])) {
            return;
        }
        if (count($explode) === 0) {
            unset($this->associations[$baseAssociationName]);
        } else {
            $this->associations[$baseAssociationName]->without(implode('.', $explode));
        }
    }

    /**
     * @return array
     */
    public function getAssociated(): array
    {
        $result = [];
        foreach ($this->associations as $name => $associatedFactory) {
            $result[$name] = $associatedFactory->getMarshallerOptions();
        }

        return array_merge_recursive($result, $this->manualAssociations);
    }

    /**
     * @return array<\CakephpFixtureFactories\Factory\BaseFactory>
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    /**
     * Add an associated factory to the BaseFactory
     *
     * @param string $associationName Association
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Factory
     * @return void
     */
    public function addAssociation(string $associationName, BaseFactory $factory): void
    {
        $this->associations[$associationName] = $factory;
    }

    /**
     * @return \Cake\ORM\Table
     */
    public function getTable(): Table
    {
        return $this->getFactory()->getTable();
    }

    /**
     * @param array $associations
     * @return void
     */
    public function addManualAssociations(array $associations): void
    {
        $this->manualAssociations = array_merge_recursive($associations, $this->manualAssociations);
    }
}
