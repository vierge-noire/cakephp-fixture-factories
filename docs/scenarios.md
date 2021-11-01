<h1 align="center">Scenarios</h1>

You can create scenarios that will persist a multitude of test fixtures. This can be useful to seed your
test database with a reusable set of data.

Use the `CakephpFixtureFactories\Scenario\ScenarioAwareTrait`
in your test and load your scenario with the `loadFixtureScenario()` method. You can either provide the
fully qualified name of the scenario class, or place your scenarios under the `App\Test\Scenario` namespace.


Example:
```php
$authors = $this->loadFixtureScenario('NAustralianAuthors', 3);
```
will persist 3 authors associated to the country Australia, as defined [in this example scenario](tests/Scenario/NAustralianAuthorsScenario.php).

Scenarios should implement the `CakephpFixtureFactories\Scenario\FixtureScenarioInterface` class.
[This test](tests/TestCase/Scenario/FixtureScenarioTest.php) provides an example on how to use scenarios.
