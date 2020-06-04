## Test life cycle

Here is the only step performed by the Fixture Factories Fixture Manager, and how to disable it.

### Truncating tables

The Fixture Manager truncates the dirty tables at the beginning of each test. This is the only action performed.

By dirty tables is meant tables on which a the primary key has been incremented at least one.
Therefore an empty table may be considered as dirty. 

### Disabling the truncation

You may wish to skip the truncation of tables between the tests. For example if you know in advance that
your tests do not interact with the database, or if you do not mind not having a dirty DB at the beginning of your tests.
This can be achieved at the test class level, by letting your test class using the trait `CakephpFixtureFactories\TestSuite\SkipTablesTruncation`.

### Using CakePHP fixtures

It is still possible to use the native CakePHP fixtures. To this aim, you may simply load them as described [here](https://book.cakephp.org/3/en/development/testing.html#creating-fixtures).
This will have a slight impact on the speed of your tests. You may consider in such cases disabling the truncation
of tables between each test as described above. The CakePHP fixtures will handle that.

We however discourage using both Fixture Factories and CakePHP Fixtures within one single Test Class.
It is possible, but may lead to confusion for the developer. 

***Note: you should not add the [CakePHP native listener](https://book.cakephp.org/3/en/development/testing.html#phpunit-configuration)*** to your `phpunit.xml` file.
Only one listener is required, which is the one described in the [present documentation](https://github.com/pakacuda/cakephp-fixture-factories/blob/master/docs/setup.md#listeners).

