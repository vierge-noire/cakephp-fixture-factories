<?php
declare(strict_types=1);

namespace CakephpFixtureFactories;

use Cake\Core\BasePlugin;

/**
 * Plugin class for migrations
 */
class Plugin extends BasePlugin
{
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name = 'CakephpFixtureFactories';

    /**
     * Don't try to load routes.
     *
     * @var bool
     */
    protected $routesEnabled = false;
}
