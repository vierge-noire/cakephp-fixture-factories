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
namespace CakephpFixtureFactories\ORM;

use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\TableRegistry;

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
 *
 * @internal
 */
class FactoryTableRegistry extends TableRegistry
{
    /**
     * Default LocatorInterface implementation class.
     *
     * @var string
     */
    protected static $_defaultLocatorClass = FactoryTableLocator::class;

    /**
     * @var null
     */
    protected static $_locator;

    /**
     * Returns a singleton instance of LocatorInterface implementation.
     *
     * A new LocatorClass is returned. This is very important in regards
     * to the handling of events
     *
     * @return LocatorInterface
     */
    public static function getTableLocator()
    {

        if (!isset(self::$_locator)) {
            self::$_locator = new static::$_defaultLocatorClass();
        }

        return self::$_locator;
    }
}
