## Test life cycle

Here is the only step performed by the Fixture Factories Fixture Manager, and how to disable it.

### Truncating tables

The Fixture Manager truncates the dirty tables at the beginning of each test. This is the only action performed.

Dirty tables are tables on which the primary key has been incremented at least one. The detection of dirty tables is made
by SQL queries. These are called `TableSniffers` and there are located in the `src/TestSuite/Sniffer` folder
 of the package. These are provided for:
* Sqlite
* MySQL
* Postgres

If you use a different database engine, you will have to provide your own. It should extend
the `BaseTableSniffer`.

You should then map in your `config/app.php` file the driver to
the custom table sniffer for each relevant connection. E.g.:
```$xslt
In config/app.php
<?php
...
'test' => [
    'className' => Connection::class,
    'driver' => Mysql::class,
    'persistent' => false,
    ...
    'tableSniffer' => '\Your\Custom\Table\Sniffer'
],
```
See the documentation of the [test suite light](https://github.com/vierge-noire/cakephp-test-suite-light#truncating-tables) for deeper insight.

### Disabling the truncation

You may wish to skip the truncation of tables between the tests. For example if you know in advance that
your tests do not interact with the database, or if you do not mind having a dirty DB at the beginning of your tests.
This is made at the test class level, by letting your test class using the trait `CakephpTestSuiteLight\SkipTablesTruncation`.

### Using CakePHP fixtures

It is still possible to use the native CakePHP fixtures. To this aim, you may simply load them as described [here](https://book.cakephp.org/3/en/development/testing.html#creating-fixtures).
This will have a slight impact on the speed of your tests. You may consider in such cases disabling the truncation
of tables between each test as described above.

We however discourage using both Fixture Factories and CakePHP Fixtures within one single Test Class.
It is possible, but may lead to confusion for the developer. 

***Note: you should not add the [CakePHP native listener](https://book.cakephp.org/3/en/development/testing.html#phpunit-configuration)*** to your `phpunit.xml` file.
Only one listener is required, which is the one described in the [present documentation](https://github.com/vierge-noire/cakephp-fixture-factories/blob/master/docs/setup.md#listeners).

