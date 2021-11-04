<?php

declare(strict_types=1);

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Faker\Generator;

/**
 * DogFactory
 *
 * @method \App\Model\Entity\Dog getEntity()
 * @method \App\Model\Entity\Dog[] getEntities()
 * @method \App\Model\Entity\Dog|\App\Model\Entity\Dog[] persist()
 * @method static \App\Model\Entity\Dog get(mixed $primaryKey, array $options = [])
 */
class DogFactory extends CakephpBaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Dogs';
    }

    /**
     * Defines the factory's default values. This is useful for
     * not nullable fields. You may use methods of the present factory here too.
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'id' => $faker->randomNumber(2),
                'name' => $faker->name,
            ];
        })->with('Country');
    }
}
