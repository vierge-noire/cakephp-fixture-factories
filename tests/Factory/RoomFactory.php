<?php

declare(strict_types=1);

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Faker\Generator;

class RoomFactory extends CakephpBaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Rooms';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'id' => $faker->randomNumber(2),
                'name' => $faker->name,
            ];
        })
            ->with('Cats.Country')
            ->with('Dogs.Country');
    }
}
