<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CityFactory extends BaseFactory
{
    protected $uniqueProperties = [
        'virtual_unique_stamp',
    ];

    protected function getRootTableRegistryName(): string
    {
        return 'Cities';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'name' => $faker->city,
            ];
        })
        ->withCountry();
    }

    /**
     * @param array|callable|null|int $parameter
     * @return $this
     */
    public function withCountry($parameter = null)
    {
        return $this->with('Country', CountryFactory::make($parameter));
    }
}
