<h1 align="center">Setup</h1>

## Non-CakePHP apps
For non-CakePHP applications, you may use the method proposed by your framework
to manage the test database, or opt for the universal
[test database cleaner](https://github.com/vierge-noire/test-database-cleaner).

You should define your DB connections in your test `bootstrap.php` file as described
in the [cookbook](https://book.cakephp.org/4/en/orm/database-basics.html#configuration).

## CakePHP apps

To be able to bake your factories,
load the CakephpFixtureFactories plugin in your `src/Application.php` file:
```php
protected function bootstrapCli(): void
{
    // Load more plugins here
    if (Configure::read('debug')) {
        $this->addPlugin('CakephpFixtureFactories');
    }
}
```

**We recommend using migrations for managing the schema of your test DB with the [CakePHP Migrator tool.](https://book.cakephp.org/migrations/2/en/index.html#using-migrations-for-tests)**

## Table Truncation

Generated fixtures usually must be removed from the database in between tests in order to avoid collisions between entities, which can cause unexpected test results.
There are several ways to manage this behavior when using fixtures and fixture factories.

### CakePHP 3 or < 4.3
For CakePHP anterior to 4.3 applications, you will need to use the [CakePHP test suite light plugin](https://github.com/vierge-noire/cakephp-test-suite-light#cakephp-test-suite-light)
to clean up the test database prior to each test.

Make sure you **replace** the native CakePHP listener with the following one inside your `phpunit.xml` (or `phpunit.xml.dist`) config file,
per default located in the root folder of your application:

```xml
<!-- Setup a listener for fixtures -->
     <listeners>
         <listener class="CakephpTestSuiteLight\FixtureInjector">
             <arguments>
                 <object class="CakephpTestSuiteLight\FixtureManager"/>
             </arguments>
         </listener>
     </listeners>
``` 

*Hint: The following command will make the required changes for you:*

```css
bin/cake fixture_factories_setup
```

You can specify a plugin (`-p`) and a specific file (`-f`), if different from `phpunit.xml.dist`.

Between each test, the package will truncate all the test tables that have been used during the previous test.

### CakePHP 4.3+
CakePHP 4.3 ships with [Fixture State Managers](https://book.cakephp.org/4/en/development/testing.html#fixture-state-managers) and provides the `TruncateStrategy`
(truncate all tables after test run) as well as the `TransactionStrategy` (create a transaction and roll it back after each test run).

The [CakePHP test suite light plugin](https://github.com/vierge-noire/cakephp-test-suite-light#cakephp-test-suite-light) provides the `TriggerStrategy` 
which will set up a trigger in your database to clean up the tables after each test run. **We recommend using this fixture strategy alongside this plugin.**

To use it, add this to the top of each test case that manages fixtures:
```php
use TruncateDirtyTables
``` 

No modification to your PHPUnit configuration is required when using the trait.

**We recommend using migrations for managing the schema of your test DB with the [CakePHP Migrator tool.](https://book.cakephp.org/migrations/2/en/index.html#using-migrations-for-tests)**



