<?php

namespace TestFixtureFactories\ORM\TableRegistry;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use function strpos;
use Cake\ORM\TableRegistry;
use TestFixtureFactories\ORM\Locator\FactoryTableLocator;

/**
 * Alternative TableRegistry to be used by fixture factories
 * The table registered here will be stripped down versions of their TableRegistry counterpart
 * The things that will be removed :
 * - Behaviors
 * - Events
 * - Validation
 *
 * The goal is twofold :
 * - Having factories without side effects. they are only meant to create fixture data in the database and are not
 *   supposed to behave like the Application's Table classes
 * - Speeding up the fixture data injection to the database
 *
 * Class FactoryTableRegistry
 * @package TestFixtureFactories\ORM\TableRegistry
 */
class FactoryTableRegistry extends TableRegistry
{
    /**
     * LocatorInterface implementation instance.
     *
     * @var \Cake\ORM\Locator\LocatorInterface
     */
    protected static $_locator;

    /**
     * Default LocatorInterface implementation class.
     *
     * @var string
     */
    protected static $_defaultLocatorClass = FactoryTableLocator::class;
}
