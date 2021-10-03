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

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

/**
 * Class AddressFactory
 * @method \TestApp\Model\Entity\Address getEntity()
 * @method \TestApp\Model\Entity\Address[] getEntities()
 * @method \TestApp\Model\Entity\Address|\TestApp\Model\Entity\Address[] persist()
 * @method static \TestApp\Model\Entity\Address get(mixed $primaryKey, array $options = [])
 */
class AddressFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Addresses';
    }

    protected function setDefaultTemplate()
    {
        $this
            ->setDefaultData(function(Generator $faker) {
                return [
                    'street' => $faker->streetAddress,
                ];
            })
            ->withCity();
    }

    public function withCity($parameter = null)
    {
        return $this->with('City', CityFactory::make($parameter));
    }
}
