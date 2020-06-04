# CakePHP Fixture Factories

This package provides an alternative approach of managing test fixtures in a [CakePHP](https://book.cakephp.org/4/en/development/testing.html) application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in cakephp.
Using factories for managing fixtures has many advantages in terms of maintenance, test performance and readability inside your tests.


The Fixture Factories
* increase the speed of your tests,
* reduce the effort of writing and maintaining tests,
* enhance the readability of your tests: you get what you see, no hidden fixtures.

You will never have to create, maintain or declare any test fixtures again.

The package is compatible with the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures).
You may continue using them along with the Fixture Factories. It is however recommended, for performance reasons, to purely switch to
the Fixture Factories approach. 

[Here is a presentation](https://www.youtube.com/watch?v=a7EQvHkIb60&t=107m54s) held at the CakePHP online Meetup on 29th April 2020.

[Here is a video](https://www.youtube.com/watch?v=x2JV59SmDik) on the motivations behind the present package.
There will be more coming soon.

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

## [The Test Lifecycle](docs/lifecycle.md)

The only step performed in the test suite is to truncate *dirty* tables before each test.

## Authors
* Nicolas Masson
* Juan Pablo Ramìrez 
