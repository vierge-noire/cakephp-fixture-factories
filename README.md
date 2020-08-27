# CakePHP Fixture Factories

This package provides an alternative approach of managing test fixtures in a [CakePHP](https://book.cakephp.org/4/en/development/testing.html) application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in CakePHP.

The CakePHP Fixture Factories plugin
* increases the speed of your tests,
* reduces the effort of writing and maintaining tests,
* enhances the readability of your tests: you get what you seed in your test, tests do not share test fixtures,
* offers to manage your test DB schema with the same migrations you use on your regular DB.

You will never have to create, maintain or declare any test fixtures again.

The package is compatible with the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures).
You may continue using them along with the Fixture Factories, these will work just as before. It is however recommended to migrate to
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

Setup a listener for fixtures replacing on `phpunit.xml.dist`:
```
<!-- Setup a listener for fixtures -->
<listeners>
    <listener class="Cake\TestSuite\Fixture\FixtureInjector">
        <arguments>
            <object class="Cake\TestSuite\Fixture\FixtureManager"/>
        </arguments>
    </listener>
</listeners>
```
to:
```
<!-- Setup a listener for fixtures -->
<listeners>
    <listener class="CakephpFixtureFactories\TestSuite\FixtureInjector">
        <arguments>
            <object class="CakephpFixtureFactories\TestSuite\FixtureManager" />
        </arguments>
    </listener>
</listeners>
```

In order to bake your factories, ensure the CakephpFixtureFactories Plugin is loaded in your `src/Application.php` file:
```
protected function bootstrapCli(): void
{
    // Load more plugins here
    $this->addPlugin('CakephpFixtureFactories');
}
```

This is also illustrated, along with the usage of migrations, in [this video](https://www.youtube.com/watch?v=h8A3lHrwInI).

## [Use Migrations](docs/migrator.md)

Take full advantage of the [Phinx migrations](https://book.cakephp.org/migrations/3/en/index.html) in order to maintain the schema
of your test DB. This is optional, but __highly recommended__.

## [Baking Fixture Factories](docs/bake.md)

Load the plugin and create all your factories in one command line.
```$xslt
bin/cake bake fixture_factory -h
```

## [Inside Fixture Factories](docs/factories.md)

What the Fixture Factories look like, and how they can incoporate your business logic.

## [Creating Test Fixtures](docs/examples.md)

In this section, we'll see how to use them.
Here is a quick example of persisting five articles having each three different authors, each with different addresses, in different cities, but all located in Kenya.
```$xslt
$article = ArticleFactory::make(5)->with('Authors[3].Address.City.Country', ['name' => 'Kenya'])->persist();
```

## [Test Lifecycle](docs/lifecycle.md)

The only step performed by the package's test suite is to truncate *dirty* tables before each test.

## License

The CakePHPFixtureFactories plugin is offered under an [MIT license](https://opensource.org/licenses/mit-license.php).

Copyright 2020 Juan Pablo Ramirez and Nicolas Masson

Licensed under The MIT License Redistributions of files must retain the above copyright notice.

## Authors
* Juan Pablo Ramirez
* Nicolas Masson
