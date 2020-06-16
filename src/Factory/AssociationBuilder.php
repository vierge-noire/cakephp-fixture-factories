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
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Association\HasOne;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use CakephpFixtureFactories\Util;

class AssociationBuilder
{
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
     * @return bool
     */
    public function checkAssociation(string $associationName): bool
    {
        try {
            $association = $this->getFactory()->getTable()->getAssociation($associationName);
        } catch (\Exception $e) {
            throw new AssociationBuilderException($e->getMessage());
        }
        if ($association instanceof HasOne || $association instanceof BelongsTo || $association instanceof HasMany || $association instanceof BelongsToMany) {
            return true;
        } else {
            throw new AssociationBuilderException("Unknown association type $association on table {$this->getFactory()->getTable()}");
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
        $table = $this->getFactory()->getTable()->getAssociation($firstAssociation)->getClassName() ?? $this->getFactory()->getTable()->getAssociation($firstAssociation)->getName();

        if (!empty($associations)) {
            $factory = $this->getFactoryFromTableName($table);
            $factory->with(implode('.', $associations), $data);
        } else {
            $factory = $this->getFactoryFromTableName($table, $data);
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
     * @return BaseFactory
     */
    public function getFactory(): BaseFactory
    {
        return $this->factory;
    }
}