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
 * Class AuthorFactory
 * @method \TestApp\Model\Entity\Author getEntity()
 * @method \TestApp\Model\Entity\Author[] getEntities()
 * @method \TestApp\Model\Entity\Author|\TestApp\Model\Entity\Author[] persist()
 * @method static \TestApp\Model\Entity\Author get(mixed $primaryKey, array $options = [])
 */
class AuthorFactory extends BaseFactory
{
    protected $skippedSetters = [
        'field_with_setter_1',
    ];

    protected function getRootTableRegistryName(): string
    {
        return "Authors";
    }

    protected function setDefaultTemplate()
    {
        $this
            ->setDefaultData(function (Generator $faker) {
                return [
                    'name' => $faker->name,
                    'field_with_setter_1' => $faker->word,
                    'field_with_setter_2' => $faker->word,
                    'field_with_setter_3' => $faker->word,
                ];
            })
            ->withAddress();
    }

    public function withArticles($parameter = null, int $n = 1)
    {
        return $this->with('Articles', ArticleFactory::make($parameter, $n)->without('Authors'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('Address', AddressFactory::make($parameter));
    }

    public function fromCountry(string $name)
    {
        return $this->with('Address.City.Country', CountryFactory::make(compact('name')));
    }
}
