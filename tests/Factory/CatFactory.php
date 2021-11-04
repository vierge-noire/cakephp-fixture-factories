<?php

declare(strict_types=1);

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Faker\Generator;

/**
 * CatFactory
 *
 * @method \App\Model\Entity\Cat getEntity()
 * @method \App\Model\Entity\Cat[] getEntities()
 * @method \App\Model\Entity\Cat|\App\Model\Entity\Cat[] persist()
 * @method static \App\Model\Entity\Cat get(mixed $primaryKey, array $options = [])
 */
class CatFactory extends CakephpBaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Cats';
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
