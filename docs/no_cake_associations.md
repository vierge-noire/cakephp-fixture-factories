<h1 style="text-align: center">Associations for non-CakePHP apps</h1>

Associations can be defined within the factories in the `initialize()` method.
The `getTable()` method provides public access to the model class used by the factories. If not defined in your application
(which is probably the case if not built with CakePHP), the model class is generated automatically,
based on the table name returned by the `getRootTableRegistryName` method.

For example, considering the following schema:

| addresses | cities     | countries |
|-----------|------------|-----------|
| id        | id         | id        |
| street    | name       | name      |
| city_id   | country_id | created   |
| created   | created    | modified  |
| modified  | modified   |           |

First create the `CityFactory`, `AddressFactory` and `CountryFactory` classes as described [here](./factories.md).

In the `CityFactory`, you may then associate the `cities`
belonging to a `country` and having many `addresses` in the `initialize` method:

```php
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CityFactory extends BaseFactory
{
    protected function initialize(): void
    {
        $this->getTable()
            ->belongsTo('Country')
            ->hasMany('Addresses');
    }
    
    protected function getRootTableRegistryName(): string
    {
        return "Cities";
    }   
    
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'name' => $faker->city(),
            ];
        })
        ->withCountry();
    }
}
```

Once this is defined, you may then call:
```php
$city = CityFactory::make()
    ->with('Addresses', 4)
    ->with('Country', ['name' => 'India'])
    ->getEntity();
```
which will set the city's country, and provide 4 random addresses.

You will find described in the cookbook [HERE](https://book.cakephp.org/4/en/orm/associations.html) how to define your associations.
Non CakePHP applications will not need to create any table objects, but rather use the `getTable()` public method.
