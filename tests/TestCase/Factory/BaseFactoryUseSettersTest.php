<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.7.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;

class BaseFactoryUseSettersTest extends TestCase
{
    public function testNonStringField()
    {
        $this->expectException(FixtureFactoryException::class);
        AuthorFactory::make()->skipSetterFor(0);
    }

    public function testSettersAreDefinedOnOneAuthor()
    {
        $value = 'Foo';
        $author = AuthorFactory::make([
            'field_with_setter_1' => $value,
            'field_with_setter_2' => $value,
            'field_with_setter_3' => $value,
        ])->skipSetterFor([])->getEntity();

        for($i=1;$i<4;$i++) {
            $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_$i"));
        }
    }

    public function testSettersAreDefinedOnTwoAuthors()
    {
        $value = 'Foo';
        $authors = AuthorFactory::make([
            'field_with_setter_1' => $value,
            'field_with_setter_2' => $value,
            'field_with_setter_3' => $value,
        ], 2)->skipSetterFor([])->getEntities();

        foreach ($authors as $author) {
            for($i=1;$i<4;$i++) {
                $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_$i"));
            }
        }
    }

    public function testSettersAreDefinedOnAssociatedAuthor()
    {
        $value = 'Foo';
        $authorFactory = AuthorFactory::make(4)
            ->patchData([
                'field_with_setter_1' => $value,
                'field_with_setter_2' => $value,
                'field_with_setter_3' => $value,
            ])
            ->skipSetterFor([]);
        $authors = ArticleFactory::make()->with('Authors', $authorFactory)->getEntity()->authors;

        foreach ($authors as $author) {
            for($i=1;$i<4;$i++) {
                $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_$i"));
            }
        }
    }

    public function testSetterIsSkippedForDefaultFields()
    {
        $value = 'Foo';
        $author = AuthorFactory::make([
            'field_with_setter_1' => $value,
            'field_with_setter_2' => $value,
            'field_with_setter_3' => $value,
        ])->getEntity();

        $this->assertSame($value, $author->get("field_with_setter_1"));
        $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_2"));
        $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_3"));
    }

    public function testSettersAreSkippedDefinedOnTwoAuthors()
    {
        $value = 'Foo';
        $authors = AuthorFactory::make([
            'field_with_setter_1' => $value,
            'field_with_setter_2' => $value,
            'field_with_setter_3' => $value,
        ], 2)->skipSetterFor('field_with_setter_2')->getEntities();

        foreach ($authors as $author) {
            $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_1"));
            $this->assertSame($value, $author->get("field_with_setter_2"));
            $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_3"));
        }
    }

    public function testSettersAreSkippedOnAssociatedAuthor()
    {
        $value = 'Foo';
        $authorFactory = AuthorFactory::make(4)
            ->patchData([
                'field_with_setter_1' => $value,
                'field_with_setter_2' => $value,
                'field_with_setter_3' => $value,
            ])
            ->skipSetterFor(['field_with_setter_2', 'field_with_setter_3']);
        $authors = ArticleFactory::make()->with('Authors', $authorFactory)->getEntity()->authors;

        foreach ($authors as $author) {
            $this->assertSame($author->prependPrefixToField($value), $author->get("field_with_setter_1"));
            $this->assertSame($value, $author->get("field_with_setter_2"));
            $this->assertSame($value, $author->get("field_with_setter_3"));
        }
    }
}
