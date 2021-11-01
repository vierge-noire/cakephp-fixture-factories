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
 * Class CountryFactory
 *
 * @method \TestApp\Model\Entity\Country getEntity()
 * @method \TestApp\Model\Entity\Country[] getEntities()
 * @method \TestApp\Model\Entity\Country|\TestApp\Model\Entity\Country[] persist()
 * @method static \TestApp\Model\Entity\Country get(mixed $primaryKey, array $options = [])
 */
class CountryFactory extends BaseFactory
{
    protected $uniqueProperties = [
        'unique_stamp',
    ];

    protected function getRootTableRegistryName(): string
    {
        return 'Countries';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'name' => $faker->country,
                'unique_stamp' => $faker->uuid,
            ];
        });
    }
}
