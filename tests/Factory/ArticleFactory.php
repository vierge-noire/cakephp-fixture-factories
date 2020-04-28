<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

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
    protected function setDefaultTemplate()
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'title' => $faker->lastName
            ];
        })
        ->withAuthors();
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
        return $this->with('Bills', BillFactory::make($parameter, $n)->without('article'));
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
