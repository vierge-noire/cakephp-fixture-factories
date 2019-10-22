# test-fixture-factories

This is the B-Project made Fixture Manager.

In order to use the Newton Fixture Manager:

* Use the NewtonFixtureManager trait in your test classes.
* Replace the fixture manager and injector in the phpunit.xml config by the ones provided in the present vendor.
* Run the FixtureManagerShell->init() method in your test's bootstrap 
 
By doing so, the schema of your test DB is updated during the bootstrapping of your tests.

All the test tables are truncated before each test.