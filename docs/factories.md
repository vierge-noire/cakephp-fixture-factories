## Fixture Factories

### What they look like

A factory is a class that extends the `CakephpFixtureFactories\Factory\BaseFactory`. It should implement the following two methods:
* `getRootTableRegistryName()`  which indicates the model that the factory will use to buld its fixtures;
* `setDefaultTemplate()`  which sets the default configuration of each entity created by the factory.

The `Faker\Generator` is used in order to randomly populate fields, and is anytime available using `$this->getFaker()`.

[Here is further documentation on Fake](https://github.com/fzaninotto/Faker). 

Let us consider for example a model `Articles`, related to multiple `Authors`.

This could be for example the `ArticleFactory`. Per default the fields `title` and `body` are set with `Faker` and two associated `authors` are created.
```$xslt
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class ArticleFactory extends BaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return "Articles";
    }

    /**
     * Defines the default values of you factory. Useful for
     * not nullable fields.
     * Use the patchData method to set the field values.
     * You may use methods of the factory here
     * @return self
     */
    protected function setDefaultTemplate(): void
    {
          $this->setDefaultData(function(Generator $faker) {
               return [
                    'title' => $faker->text(30),
                    'body'  => $faker->text(1000),
               ];
          })
          ->withAuthors(2);
    }

    public function withAuthors($parameter = null, int $n = 1): self
    {
        return $this->with('Authors', AuthorFactory::make($parameter, $n));
    }

    /**
     * Set the Article's title as a random job title     
     * @return ArticleFactory
     */
    public function setJobTitle()
    {
        return $this->patchData([
            'title' => $this->getFaker()->jobTitle,
        ]);
    }
}
```
You may add any methods related to your business model, such as `setJobTitle` to help you build efficient and reusable factories.

### Required fields

If a field is required in the database, it will have to be populated in the `setDefaultTemplate` method. You may simply set it to a fixed value, for example 1.

### Locale

The factories will generate data in the locale of your application, if the latter is supported by faker.

### Validation / Behaviors
With the aim of persisting data in the database as straighforwardly as possible, all behaviors (except Timestamp) and all validations
are deactivated when creating CakePHP entities and persisting them to the database. Validation may be reactivated / customized by overwriting
 the properties `$marshallerOptions` and `$saveOptions` in the factory concerned.
 
 ### Model events
 Per default, all model events related to a factory's root table are switched off. This will have an impact on
 a model's behavior actions.
 This is made in order to save the test fixtures in the test database as fast and straightforwardly as possible.
 
 It is possible to create test fixtures with the model events activated as follows:
 ```
$article = ArticleFactory::makeWithModelEvents()->persist();
```
 
 The static method `makeWithModelEvents` accepts the same arguments as the method `make`.
 
 ### Namespace
 
 Assuming your application namespace in `App`, factories should be placed in the `App\Test\Factory` namespace of your application.
 Or for a plugin Foo, in `Foo\Test\Factory`.
 
 You may change that by setting in your configuration the key `TestFixtureNamespace` to the desired namespace.

 ### Property uniqueness

It is not rare to have to create entities associated with an entity that should remain
constant and should not be recreated once it was already persisted. For example, if you create
5 cities within a country, you will not want to have 5 countries created. This might 
collide with the constrains of your schema. The same goes of course with primary keys.

The fixture factories offer to define unique properties, under the protected property
$uniqueProperties. For example given a country factory. 

```$xslt
namespace App\Test\Factory;
... 
class CountryFactory extends BaseFactory
{
    protected $uniqueProperties = [
        'name',
    ];
...
}
```

Knowing the property `name` is unique, the country factory
will be cautious whenever the property `name` is set by the developer.

Executing `CityFactory::make(5)->with('Country', ['name' => 'Foo'])->persist()` will create
5 cities all associated to one unique country. If you perform that same operation again,
you will have 10 cities, all associated to one single country.

### Primary keys uniqueness

The uniqueness of the primary keys is handled exactely the same way as described above,
with the particularity that you do not have to define them as unique. The factory
cannot read the uniqueness of a property in the schema, but it knows which properties
are primary keys. Therefore, executing
`CityFactory::make(5)->with('Country', ['myPrimaryKey' => 1])->persist()` will behave the
same as if the primary key `myPrimaryKey` had been defined unique. In short, the factories
do the job for you. 

### Next
 
 Let us now see [how to use them](examples.md)...
