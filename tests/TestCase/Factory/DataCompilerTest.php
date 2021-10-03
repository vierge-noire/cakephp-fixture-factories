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

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Factory\DataCompiler;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpTestSuiteLight\SkipTablesTruncation;
use TestApp\Model\Table\PremiumAuthorsTable;

class DataCompilerTest extends TestCase
{
    use SkipTablesTruncation;

    /**
     * @var DataCompiler
     */
    public $authorDataCompiler;
    /**
     * @var DataCompiler
     */
    public $articleDataCompiler;

    public function setUp(): void
    {
        $this->authorDataCompiler = new DataCompiler(AuthorFactory::make());
        $this->articleDataCompiler = new DataCompiler(ArticleFactory::make());

        parent::setUp();
    }

    public function testGetMarshallerAssociationNameShouldThrowInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->authorDataCompiler->getMarshallerAssociationName('business_address');
    }

    public function testGetMarshallerAssociationNameShouldReturnUnderscoredAssociationName()
    {
        $marshallerAssociationName = $this->authorDataCompiler->getMarshallerAssociationName('BusinessAddress');
        $this->assertSame('business_address', $marshallerAssociationName);
    }

    public function testGetMarshallerAssociationNameWithDottedAssociation()
    {
        $marshallerAssociationName = $this->authorDataCompiler->getMarshallerAssociationName('BusinessAddress.City.Country');
        $this->assertSame('business_address.city.country', $marshallerAssociationName);
    }

    public function testGetMarshallerAssociationNameWithAliasedAssociationName()
    {
        $marshallerAssociationName = $this->articleDataCompiler->getMarshallerAssociationName('ExclusivePremiumAuthors');
        $this->assertSame(PremiumAuthorsTable::ASSOCIATION_ALIAS, $marshallerAssociationName);
    }

    public function testGetMarshallerAssociationNameWithAliasedDeepAssociationName()
    {
        $marshallerAssociationName = $this->articleDataCompiler->getMarshallerAssociationName('ExclusivePremiumAuthors.Address');
        $this->assertSame(PremiumAuthorsTable::ASSOCIATION_ALIAS . '.address', $marshallerAssociationName);
    }

    public function testGenerateRandomPrimaryKeyInteger()
    {
        $this->assertTrue(is_int($this->articleDataCompiler->generateRandomPrimaryKey('integer')));
    }

    public function testGenerateRandomPrimaryKeyBigInteger()
    {
        $this->assertTrue(is_int($this->articleDataCompiler->generateRandomPrimaryKey('biginteger')));
    }

    public function testGenerateRandomPrimaryKeyUuid()
    {
        $this->assertTrue(is_string($this->articleDataCompiler->generateRandomPrimaryKey('uuid')));
    }

    public function testGenerateRandomPrimaryKeyWhateverColumnType()
    {
        $this->assertTrue(is_int($this->articleDataCompiler->generateRandomPrimaryKey('foo')));
    }

    public function testGenerateArrayOfRandomPrimaryKeys()
    {
        $res = $this->articleDataCompiler->generateArrayOfRandomPrimaryKeys();
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_int($res['id']));
        $this->assertSame(1, count($res));
    }

    public function testCreatePrimaryKeyOffset()
    {
        $res = $this->articleDataCompiler->createPrimaryKeyOffset();
        $this->assertTrue(is_array($res));
        $this->assertTrue(is_int($res['id']));
        $this->assertSame(1, count($res));

        $this->expectException(PersistenceException::class);
        $this->articleDataCompiler->createPrimaryKeyOffset();
    }

    public function testSetPrimaryKey()
    {
        $data = CountryFactory::make()->getEntity();

        $this->articleDataCompiler->startPersistMode();
        $res = $this->articleDataCompiler->setPrimaryKey($data);
        $this->articleDataCompiler->endPersistMode();
        $this->assertTrue(is_int($res['id']));
    }

    /**
     * If the id is set be the user, the primary key is set to this id
     * No random primary key is generated
     */
    public function testSetPrimaryKeyWithIdSet()
    {
        $id = rand(1, 10000);
        $entity = new Entity(compact('id'));
        $res = $this->articleDataCompiler->setPrimaryKey($entity);
        $this->assertSame($id, $res['id']);
    }

    public function testSetPrimaryKeyOnEntity()
    {
        $countries = CountryFactory::make(2)->getEntity();

        $this->articleDataCompiler->startPersistMode();
        $res = $this->articleDataCompiler->setPrimaryKey($countries);

        $this->assertTrue(is_int($res['id']));

        $this->articleDataCompiler->endPersistMode();
    }

    public function dataForGetModifiedUniqueFields(): array
    {
        return [
            [[], []],
            [['id' => 'Foo',], ['id']],
            [['id' => 'Foo', 'name' => 'Bar'], ['id']],
            [['id' => 'Foo', 'name' => 'Bar', 'unique_stamp' => 'FooBar'], ['id', 'unique_stamp']],
        ];
    }

    /**
     * @dataProvider dataForGetModifiedUniqueFields
     * @param array $injectedData
     * @param array $expected
     */
    public function testGetModifiedUniqueFields(array $injectedData, array $expected)
    {
        $dataCompiler = new DataCompiler(CountryFactory::make($injectedData));
        $dataCompiler->compileEntity($injectedData);
        $this->assertSame($dataCompiler->getModifiedUniqueFields(), $expected);
    }
}
