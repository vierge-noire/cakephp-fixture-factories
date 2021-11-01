<h1 align="center">Associations for non-CakePHP apps</h1>

Associations can be defined within the factories in the `initialize()` method.
The `getTable()` provides public access to table model used by the factories. 

For example in the cities table, you may define the association of the `cities` belonging
to a `country` as follows:

```php
// In App\Test\Factory\CityFactory

protected function initialize(): void
    {
        $this->getTable()
            ->belongsTo('Country')
            ->hasMany('Addresses');
    }
```

Once this is defined, you may then call:
```php
$city = CityFactory::make()
    ->with('Addresses', 4)
    ->with('Country', ['name' => 'India'])
    ->getEntity();
```
which will set the country where the city is created, and provide 4 random addresses.

You will find described in the cookbook [HERE](https://book.cakephp.org/4/en/orm/associations.html) how to define your associations.
Non CakePHP applications will not need to create any table objects, but rather use the `getTable()` public method.
