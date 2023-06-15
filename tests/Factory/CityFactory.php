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

/**
 * Class CityFactory
 *
 * @method \TestApp\Model\Entity\City getEntity()
 * @method \TestApp\Model\Entity\City[] getEntities()
 * @method \TestApp\Model\Entity\City|\TestApp\Model\Entity\City[] persist()
 * @method static \TestApp\Model\Entity\City get(mixed $primaryKey, array $options = [])
 */
class CityFactory extends BaseFactory
{
    protected array $uniqueProperties = [
        'virtual_unique_stamp',
    ];

    protected function initialize(): void
    {
        $this->getTable()->hasMany('TableWithoutModel', [
            'foreignKey' => 'foreign_key',
        ]);
    }

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
