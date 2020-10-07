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

use Cake\Cache\Cache;
use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Utility\Inflector;
use Cake\Utility\Security;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');
define('APP_PATH', ROOT . DS . 'TestApp' . DS);
define('VENDOR_PATH', ROOT . DS . 'vendor' . DS);
define('TEMPLATE_PATH_CAKE_4', ROOT . DS . 'templates' . DS);
define('TEMPLATE_PATH_CAKE_3', ROOT . DS . APP_DIR . DS . 'Template' . DS);

define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('CAKE_CORE_INCLUDE_PATH', VENDOR_PATH . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('CORE_TESTS', ROOT . DS . 'tests' . DS);
define('CORE_TEST_CASES', CORE_TESTS . 'TestCase');
define('TEST_APP', CORE_TESTS . 'TestApp' . DS);

// Point app constants to the test app.
define('APP', TEST_APP . 'src' . DS);
define('TESTS', TEST_APP . 'tests' . DS);
define('WWW_ROOT', TEST_APP . 'webroot' . DS);
define('CONFIG', TEST_APP . 'config' . DS);

// phpcs:disable
@mkdir(LOGS);
@mkdir(SESSIONS);
@mkdir(CACHE);
@mkdir(CACHE . 'views');
@mkdir(CACHE . 'models');
// phpcs:enable

require_once CORE_PATH . 'config/bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'TestApp',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => APP_DIR,
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [TEST_APP . 'plugins' . DS],
        'templates' => [
            TEST_APP . 'templates' . DS,
            TEMPLATE_PATH_CAKE_4,
            TEMPLATE_PATH_CAKE_3,
            \Cake\Core\Plugin::templatePath('Bake'),
        ],
        'locales' => [TEST_APP . 'resources' . DS . 'locales' . DS],
    ],
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true,
    ],
]);

// Ensure default test connection is defined
if (!getenv('DB_DRIVER')) {
    putenv('DB_DRIVER=Cake\Database\Driver\Sqlite');
}

if (!getenv('DB_USER')) {
    putenv('DB_USER=root');
}

if (!getenv('DB_PWD')) {
    putenv('DB_PWD=root');
}

if (!getenv('DB_HOST')) {
    putenv('DB_HOST=127.0.0.1');
}

$dbConnection = [
    'className' => 'Cake\Database\Connection',
    'driver' => getenv('DB_DRIVER'),
    'persistent' => false,
    'host' => getenv('DB_HOST'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PWD'),
    'database' => 'test_fixture_factories',
    'encoding' => 'utf8',
    'timezone' => 'UTC',
    'cacheMetadata' => true,
    'quoteIdentifiers' => true,
    'log' => false,
    //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
    'url' => env('DATABASE_TEST_URL', null),
];

ConnectionManager::setConfig('default', $dbConnection);
ConnectionManager::setConfig('test', $dbConnection);

// This connection is meant to be ignored
$dummyConnection = $dbConnection;
$dummyConnection['driver'] = 'Foo';
ConnectionManager::setConfig('test_dummy', $dummyConnection);


Configure::write('Session', [
    'defaults' => 'php',
]);

Log::setConfig([
    // 'queries' => [
    //     'className' => 'Console',
    //     'stream' => 'php://stderr',
    //     'scopes' => ['queriesLog']
    // ],
    'debug' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['notice', 'info', 'debug'],
        'file' => 'debug',
        'path' => LOGS,
    ],
    'error' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        'file' => 'error',
        'path' => LOGS,
    ],
]);

Chronos::setTestNow(Chronos::now());
Security::setSalt('a-long-but-not-random-value');

ini_set('intl.default_locale', 'en_US');
ini_set('session.gc_divisor', '1');

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

Inflector::rules('irregular', array(
    'contacthrdata' => 'contacthrdatas',
    'debttranche' => 'debttranches',
    'tranche' => 'tranches',
));

Inflector::rules('singular', ['/(ss)$/i' => '\1']);

\CakephpFixtureFactories\TestSuite\Migrator::migrate([
    ['connection' => 'test'],
    ['plugin' => 'TestPlugin'],
]);