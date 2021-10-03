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
 * Class CustomerFactory
 * @method \TestPlugin\Model\Entity\Customer getEntity()
 * @method \TestPlugin\Model\Entity\Customer[] getEntities()
 * @method \TestPlugin\Model\Entity\Customer|\TestPlugin\Model\Entity\Customer[] persist()
 * @method static \TestPlugin\Model\Entity\Customer get(mixed $primaryKey, array $options = [])
 */
class CustomerFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Customers';
    }

    protected function setDefaultTemplate()
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'name' => $faker->lastName,
            ];
        });
    }

    /**
     * @param array|callable|null|int|\Cake\Datasource\EntityInterface $parameter Injected data
     * @param int $n
     * @return CustomerFactory
     */
    public function withBills($parameter = null, $n = 1): CustomerFactory
    {
        return $this->with('Bills', BillFactory::make($parameter, $n)->without('Customer'));
    }

    /**
     * @param array|callable|null|int|\Cake\Datasource\EntityInterface $parameter Injected data
     * @return CustomerFactory
     */
    public function withAddress($parameter = null): CustomerFactory
    {
        return $this->with('Address', AddressFactory::make($parameter));
    }
}
