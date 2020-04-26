<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class ArticleFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "Articles";
    }

    /**
     * @return self
     */
    protected function setDefaultTemplate()
    {
        return $this
            ->patchData([
                'title' => $this->getFaker()->jobTitle
            ])
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
}
