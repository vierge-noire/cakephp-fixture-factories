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

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;

class BaseFactoryArrayNotationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Configure::write('FixtureFactories.testFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('FixtureFactories.testFixtureNamespace');
    }

    public function testBaseFactoryArrayNotation_default_value()
    {
        AuthorFactory::make()->persist();

        $author = AuthorFactory::firstOrFail();
        $this->assertSame(AuthorFactory::JSON_FIELD_DEFAULT_VALUE, $author->json_field);
    }

    public function testBaseFactoryArrayNotation_overwrite_default_value()
    {
        $value = ['c' => 'd'];
        AuthorFactory::make(['json_field' => $value])->persist();

        $author = AuthorFactory::firstOrFail();
        $this->assertSame($value, $author->json_field);
    }

    public function testBaseFactoryArrayNotation_overwrite_one_field()
    {
        $author = AuthorFactory::make(['json_field.subField1' => 'newValue'])->getEntity();

        $expectedValue = AuthorFactory::JSON_FIELD_DEFAULT_VALUE;
        $expectedValue['subField1'] = 'newValue';

        $this->assertSame($expectedValue, $author->json_field);
        $this->assertNull($author->get('json_field.subField1'));
    }

    public function testBaseFactoryArrayNotation_OverwriteMultipleSelectedNestedFields()
    {
        $author = AuthorFactory::make([
            'json_field.subField1' => 'newVal1',
            'json_field.subField2' => 'newVal2',
        ])->getEntity();

        $expectedValue = AuthorFactory::JSON_FIELD_DEFAULT_VALUE;
        $expectedValue['subField1'] = 'newVal1';
        $expectedValue['subField2'] = 'newVal2';

        $this->assertSame($expectedValue, $author->json_field);
        $this->assertNull($author->get('json_field.subField1'));
    }

    public function testBaseFactoryArrayNotation_overwrite_one_field_with_set_field()
    {
        $author = AuthorFactory::make()
            ->setField('json_field.subField1', 'newValue')
            ->getEntity();

        $expectedValue = AuthorFactory::JSON_FIELD_DEFAULT_VALUE;
        $expectedValue['subField1'] = 'newValue';

        $this->assertSame($expectedValue, $author->json_field);
        $this->assertNull($author->get('json_field.subField1'));
    }

    public function testBaseFactoryArrayNotation_overwrite_one_field_with_deep_association()
    {
        $author = AuthorFactory::make(['json_field.subField1' => [
                'subSubField1' => 'subSubValue1',
                'subSubField2' => 'subSubValue2',
            ]])
            ->setField('json_field.subField1.subSubField2', 'blah')
            ->getEntity();

        $expectedValue = [
            'subField1' => [
                'subSubField1' => 'subSubValue1',
                'subSubField2' => 'blah',
            ],
            'subField2' => 'subFieldValue2',
        ];

        $this->assertSame($expectedValue, $author->json_field);
    }

    public function testBaseFactoryArrayNotation_overwrite_one_field_with_set_field_on_association()
    {
        $article = ArticleFactory::make()->withAuthors(
                AuthorFactory::make(2)
                    ->setField('json_field.subField1', 'newValue')->getEntities()
        )->getEntity();

        $expectedValue = AuthorFactory::JSON_FIELD_DEFAULT_VALUE;
        $expectedValue['subField1'] = 'newValue';

        $this->assertSame($expectedValue, $article->authors[0]->json_field);
        $this->assertSame($expectedValue, $article->authors[1]->json_field);
        $this->assertNull($article->authors[0]->get('json_field.subField1'));
        $this->assertNull($article->authors[1]->get('json_field.subField1'));
    }

    public function testBaseFactoryArrayNotation_with_undefined_value()
    {
        $author = AuthorFactory::make()
            ->setField('non-existing_json_field.subField1', 'newValue')
            ->getEntity();

        $expectedValue = AuthorFactory::JSON_FIELD_DEFAULT_VALUE;

        $this->assertSame(['subField1' => 'newValue'], $author->get('non-existing_json_field'));
        $this->assertSame($expectedValue, $author->json_field);
    }

    public function testBaseFactoryArrayNotation_with_non_array_value()
    {
        $this->expectException(FixtureFactoryException::class);
        $this->expectExceptionMessage('Value foo cannot be merged with array notation json_field.subField1 => newValue');

        AuthorFactory::make(['json_field' => 'foo'])
            ->setField('json_field.subField1', 'newValue')
            ->getEntity();
    }
}
