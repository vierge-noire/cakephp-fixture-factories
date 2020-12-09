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

class BillFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Bills';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'amount' => $faker->numberBetween(0, 1000),
            ];
        })
        ->withArticle()
        ->withCustomer()
        ->listeningToModelEvents([
            'Model.beforeMarshal',
            'Model.afterSave',
        ]);
    }

    public function withArticle($parameter = null)
    {
        return $this->with('Article', ArticleFactory::make($parameter));
    }

    public function withCustomer($parameter = null)
    {
        return $this->with('Customer', CustomerFactory::make($parameter));
    }
}
