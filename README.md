# CakePHP Fixture Factories

This package provides an alternative approach of managing test fixtures in a [CakePHP](https://book.cakephp.org/4/en/development/testing.html) application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in CakePHP.

The Fixture Factories
* increase the speed of your tests,
* reduce the effort of writing and maintaining tests,
* enhance the readability of your tests: you get what you see, no fixtures in the bakground.

You will never have to create, maintain or declare any test fixtures again.

The package is compatible with the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures).
You may continue using them along with the Fixture Factories. It is however recommended, for performance reasons, to purely switch to
the Fixture Factories approach. 

[Here is a presentation](https://www.youtube.com/watch?v=a7EQvHkIb60&t=107m54s) held at the CakePHP online Meetup on 29th April 2020.

[Here is a serie of videos](https://www.youtube.com/playlist?list=PLYQ7YCTh-CYwL4pcDkzqHF8sv31cVd2or) on the present package.

## Installation
For CakePHP 4.x:
```
composer require --dev pakacuda/cakephp-fixture-factories
```

For CakePHP 3.x, append:  ```"^0.1.0"```

## [Setup](docs/setup.md)

Adjustments before using the fixture factories (1 minute).
This is also illustrated, along with the usage of migrations, in [this video](https://www.youtube.com/watch?v=h8A3lHrwInI).

## [Use Migrations](docs/migrator.md)

Take full advantage of the [Phinx migrations](https://book.cakephp.org/migrations/3/en/index.html) in order to maintain the schema
of your test DB. This is optional, but __highly recommended__.

## [Baking Fixture Factories](docs/bake.md)

Create all your fixtures in one command line.

## [Inside Fixture Factories](docs/factories.md)

What the Fixture Factories look like.

## [Creating Test Fixtures](docs/examples.md)

And how they work. 
Here is a quick example, detailed in this section:
```$xslt
$article = ArticleFactory::make(5)->with('Authors[3].Address.City.Country', ['name' => 'Kenya'])->persist();
```

#### On the command line:
Factories can also conveniently populate your database in order to test your application on the browser.
The following command will persist 5 articles, each with 3 irish authors, considering that the `ArticleFactory` class features
a `withThreeIrishAuthors()` method:
```$xslt
bin/cake fixture_factories_persist Authors -n 5 -m withThreeIrishAuthors
```
The option `--dry-run` or `-d` will display the output without persisting.
The option `-c` will persist in the connection provided (default is `test`).
The option `-w` will create associated fixtures.

The `fixture_factories_persist` command is featured on CakePHP 4 only (open to contribution for CakePHP 3).

#### Scenarios:

You can create scenarios that will persist a multitude of test fixtures. Use the `CakephpFixtureFactories\Scenario\ScenarioAwareTrait`
in your test and load your scenario with the `loadFixtureScenario()` method. You can either provide the
fully qualified name of the scenario class, or place your scenarios under the `App\Test\Scenario` namespace.

Scenarios should implement the `CakephpFixtureFactories\Scenario\FixtureScenarioInterface` class.
[This test](tests/TestCase/Scenario/FixtureScenarioTest.php) provides an example on how to use scenarios.

## [Test Lifecycle](docs/lifecycle.md)

The only step performed by the package's test suite is to truncate *dirty* tables before each test.

## License

The CakePHPFixtureFactories plugin is offered under an [MIT license](https://opensource.org/licenses/mit-license.php).

Copyright 2020 Nicolas Masson and Juan Pablo Ramirez

Licensed under The MIT License Redistributions of files must retain the above copyright notice.

## Authors
* Nicolas Masson
* Juan Pablo Ramirez 
