<p align="center">
    <a href="https://vierge-noire.github.io/" target="_blank"><img src="https://vierge-noire.github.io/images/fixture_factories.svg" alt="ff-logo" width="150"  /></a>
</p>
<h1 align="center">
CakePHP Fixture Factories
</h1>
An alternative approach to create test fixtures.

The CakePHP Fixture Factories plugin
* increases the speed of your tests,
* reduces the effort of writing and maintaining tests,
* enhances the readability of your tests: you get what you seed in your test, tests do not share test fixtures,
* offers to manage your test DB schema with the same migrations you use on your regular DB.

You will never have to create, maintain or declare any test fixtures again.

#### Example:

you need to create three users belonging to a group of users with a certain permission `some-permission`? Once your `UserFactory`, `GroupFactory` and `PermissionFactory` are baked, you can create your users by calling:

`$user = UserFactory::make(3)->with('Groups.Permissions', ['name' => 'some-permission'])->getEntity()`.

Or move that logic in your `UserFactory` by creating your own `withPermission` method, and call

`$users = UserFactory::make(3)->withPermission('some-permission')->getEntity()`.

Creating or persisting test data is made ridiculously simple, and your tests get readable.

Given your preferred style, you can either use static factory instance getter as above or embeds `FactoryAwareTrait` in your tests :

`$users = $this->getFactory('User', 3)->withPermission('some-permission')->getEntity()`.

## Installation
For CakePHP 4.x:
```
composer require --dev vierge-noire/cakephp-fixture-factories "^2.0"
```

For CakePHP 3.x:
```
composer require --dev vierge-noire/cakephp-fixture-factories "^1.0"
```
Note that PHP 7.0 is supported up to v1.1.x only.

## [Setup](docs/setup.md)

To be able to bake your factories or to replace automatically the test listeners in your phpunit file,
load the CakephpFixtureFactories plugin in your `src/Application.php` file:
```
protected function bootstrapCli(): void
{
    // Load more plugins here
    $this->addPlugin('CakephpFixtureFactories');
}
```

and set up the test listener in `phpunit.xml.dist` by running:
```
bin/cake fixture_factories_setup
```

You can specify a plugin (`-p`) and a file (`-f`) if it differs from `phpunit.xml.dist`.

Take full advantage of the [Phinx migrations](https://book.cakephp.org/migrations/3/en/index.html) in order to maintain the schema
of your test DB. This is optional, but __highly recommended__.

The [CakePHP Test Migrator package](https://github.com/vierge-noire/cakephp-test-migrator) will assist you in doing this very simply.

## [Baking Fixture Factories](docs/bake.md)

Load the plugin and create all your factories in one command line.
```$xslt
bin/cake bake fixture_factory -h
```

## [Inside Fixture Factories](docs/factories.md)

What the fixture factories are. Have a careful look at this section to understand the concept of the package.

Note that the package is compatible with the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures).
You may continue using them along with the Fixture Factories, these will work just as before.

## [Creating Test Fixtures](docs/examples.md)

In this section, we will see how to create test fixtures.

#### Example:
Persisting five articles having each three different authors, each with different addresses, in different cities, but all located in Kenya:
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

You can create scenarios that will persist a multitude of test fixtures. This can be useful to seed your
test database with a reusable set of data. 

Use the `CakephpFixtureFactories\Scenario\ScenarioAwareTrait`
in your test and load your scenario with the `loadFixtureScenario()` method. You can either provide the
fully qualified name of the scenario class, or place your scenarios under the `App\Test\Scenario` namespace.

Scenarios should implement the `CakephpFixtureFactories\Scenario\FixtureScenarioInterface` class.
[This test](tests/TestCase/Scenario/FixtureScenarioTest.php) provides an example on how to use scenarios.

## [Test Lifecycle](docs/lifecycle.md)

The only step performed by the package's test suite is to truncate *dirty* tables before each test.

## Authors
* Juan Pablo Ramirez
* Nicolas Masson

## Additional resources

[Here is a presentation](https://www.youtube.com/watch?v=a7EQvHkIb60&t=107m54s) held at the CakePHP online Meetup on 29th April 2020.

[Here is the CakeFest 2020 presentation](https://www.youtube.com/watch?v=PNA1Ck2-nVc&t=30s)

[Here is a serie of videos](https://www.youtube.com/playlist?list=PLYQ7YCTh-CYwL4pcDkzqHF8sv31cVd2or) on the present package.

## Contribute

The development branch is named `next` (CakePHP 4.x based). Feel free to send us your pull requests!

## Support
Contact us at vierge.noire.info@gmail.com for professional assistance.

You like our work? [![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/L3L52P9JA)

## License

The CakePHPFixtureFactories plugin is offered under an [MIT license](https://opensource.org/licenses/mit-license.php).

Copyright 2020 Juan Pablo Ramirez and Nicolas Masson

Licensed under The MIT License Redistributions of files must retain the above copyright notice.
