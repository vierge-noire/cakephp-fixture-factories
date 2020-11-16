# CakePHP Fixture Factories

This package provides an alternative approach of managing test fixtures in a [CakePHP](https://book.cakephp.org/4/en/development/testing.html) application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in CakePHP.

The CakePHP Fixture Factories plugin
* increases the speed of your tests,
* reduces the effort of writing and maintaining tests,
* enhances the readability of your tests: you get what you seed in your test, tests do not share test fixtures,
* offers to manage your test DB schema with the same migrations you use on your regular DB.

You will never have to create, maintain or declare any test fixtures again.

Example: you need to create three users belonging to a group of users with a certain permission `some-permission`? Once your `UserFactory`, `GroupFactory` and `PermissionFactory` are baked, you can create your users by calling:

`$user = UserFactory::make(3)->with('Groups.Permissions', ['name' => 'some-permission'])->getEntity()`.

Or move that logic in your `UserFactory` by creating your own `withPermission` method, and call

`$users = UserFactory::make(3)->withPermission('some-permission')->getEntity()`.

Inserting test data is made ridiculously simple, and your tests get readable.

The package is compatible with the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures).
You may continue using them along with the Fixture Factories, these will work just as before.

[Here is a presentation](https://www.youtube.com/watch?v=a7EQvHkIb60&t=107m54s) held at the CakePHP online Meetup on 29th April 2020.

[Here is a serie of videos](https://www.youtube.com/playlist?list=PLYQ7YCTh-CYwL4pcDkzqHF8sv31cVd2or) on the present package.

## Installation
For CakePHP 4.x:
```
composer require --dev vierge-noire/cakephp-fixture-factories "^2.0"
```

For CakePHP 3.x:
```
composer require --dev vierge-noire/cakephp-fixture-factories "^1.0"
```

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

This is also illustrated, along with the usage of migrations, in [this video](https://www.youtube.com/watch?v=h8A3lHrwInI).

## [Use Migrations](https://github.com/vierge-noire/cakephp-test-migrator)

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

## [Creating Test Fixtures](docs/examples.md)

In this section, we'll see how to use them.
Here is a quick example of persisting five articles having each three different authors, each with different addresses, in different cities, but all located in Kenya.
```$xslt
$article = ArticleFactory::make(5)->with('Authors[3].Address.City.Country', ['name' => 'Kenya'])->persist();
```

## [Test Lifecycle](docs/lifecycle.md)

The only step performed by the package's test suite is to truncate *dirty* tables before each test.

## Authors
* Juan Pablo Ramirez
* Nicolas Masson


## Support
Contact us at vierge.noire.info@gmail.com for professional assistance.

You like our work? Support us here https://ko-fi.com/viergenoire!

## License

The CakePHPFixtureFactories plugin is offered under an [MIT license](https://opensource.org/licenses/mit-license.php).

Copyright 2020 Juan Pablo Ramirez and Nicolas Masson

Licensed under The MIT License Redistributions of files must retain the above copyright notice.
