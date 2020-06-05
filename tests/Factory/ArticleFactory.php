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

class ArticleFactory extends BaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return "Articles";
    }

    /**
     * Defines the default values of you factory. Useful for
     * not nullable fields.
     * Use the patchData method to set the field values.
     * You may use methods of the factory here
     * @return self
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'title' => $faker->lastName
            ];
        })
        ->withAuthors(null, 2);
    }

    public function withAuthors($parameter = null, int $n = 1): self
    {
        return $this->with('Authors', AuthorFactory::make($parameter, $n));
    }


    /**
     * It is important here to stop the propagation of the default template of the bills
     * Otherways, each bills get a new Article, which is not the one produced by the present factory
     * @param $parameter
     * @param int $n
     * @return ArticleFactory
     */
    public function withBills($parameter = null, int $n = 1)
    {
        return $this->with('Bills', BillFactory::make($parameter, $n)->without('Article'));
    }

    /**
     * BAD PRACTICE EXAMPLE
     * This method will lead to inconsistencies (see $this->withBills())
     * @param $parameter
     * @param int $n
     * @return ArticleFactory
     */
    public function withBillsWithArticle($parameter = null, int $n = 1)
    {
        return $this->with('Bills', BillFactory::make($parameter, $n));
    }

    /**
     * Set the Article's title
     * @param string $title
     * @return ArticleFactory
     */
    public function withTitle(string $title)
    {
        return $this->patchData(compact('title'));
    }

    /**
     * Set the Article's title as a random job title
     * @return ArticleFactory
     */
    public function setJobTitle()
    {
        return $this->patchData([
            'title' => $this->getFaker()->jobTitle,
        ]);
    }
}
