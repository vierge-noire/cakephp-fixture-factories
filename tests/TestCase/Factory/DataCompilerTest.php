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

use CakephpFixtureFactories\Factory\DataCompiler;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\TestSuite\SkipTablesTruncation;
use PHPUnit\Framework\TestCase;

class DataCompilerTest extends TestCase
{
    use SkipTablesTruncation;

    /**
     * @var DataCompiler
     */
    public $authorDataCompiler;

    public function setUp()
    {
        $this->authorDataCompiler = new DataCompiler(AuthorFactory::make());

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
}