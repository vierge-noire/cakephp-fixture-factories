<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

/**
 * Class TableWithoutModelFactory
 *
 * @method \Cake\ORM\Entity getEntity()
 * @method \Cake\ORM\Entity[] getEntities()
 * @method \Cake\ORM\Entity|\Cake\ORM\Entity[] persist()
 * @method static \Cake\ORM\Entity get(mixed $primaryKey, array $options = [])
 */
class TableWithoutModelFactory extends BaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'table_without_model';
    }

    /**
     * Defines the default values of you factory. Useful for
     * not nullable fields.
     * Use the patchData method to set the field values.
     * You may use methods of the factory here
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'name' => $faker->text(120),
                'foreign_key' => $faker->randomNumber(),
                'binding_key' => $faker->randomNumber(),
                'country_id' => $faker->randomNumber(),
            ];
        });
    }
}
