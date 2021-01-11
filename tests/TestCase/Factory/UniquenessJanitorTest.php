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


use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\UniquenessException;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Factory\UniquenessJanitor;

class UniquenessJanitorTest extends TestCase
{
    public function dataForSanitizeEntityArrayOnPrimary()
    {
        return [
            [[], false],
            [['property_1'], true],
            [['property_2'], false],
            [['property_3'], false],
            [['property_1', 'property_2'], true],
            [['property_1' => ['some', 'associations'], 'property_2'], false],
        ];
    }

    /**
     * @Given the entities get factored as primary (not as associations)
     * @And two entities have given properties
     * @When they share a unique property
     * @Then an exception should be triggered
     *
     * @dataProvider dataForSanitizeEntityArrayOnPrimary
     * @param array $uniqueProperties
     * @param bool $expectException
     */
    public function testSanitizeEntityArrayOnPrimary(array $uniqueProperties, bool $expectException)
    {
        $factoryStub = $this->getMockBuilder(BaseFactory::class)->disableOriginalConstructor()->getMock();
        $factoryStub->method('getUniqueProperties')->willReturn($uniqueProperties);

        $entities = [
          ['property_1' => 'foo', 'property_2' => 'foo'],
          ['property_1' => 'foo', 'property_2' => 'dah'],
        ];

        if ($expectException) {
            $this->expectException(UniquenessException::class);
            $factoryName = get_class($factoryStub);
            $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of property_1 was not respected.");
        } else {
            $this->expectNotToPerformAssertions();
        }

        UniquenessJanitor::sanitizeEntityArray($factoryStub, $entities, true);
    }

    public function dataForSanitizeEntityArrayOnAssociation()
    {
        $associatedData = [
            ['property_1' => 'foo', 'property_2' => 'foo'],
            ['property_1' => 'foo', 'property_2' => 'dah']
        ];
        return [
            [[], $associatedData],
            [['property_1'], [$associatedData[0]]],
            [['property_2'], $associatedData],
            [['property_3'], $associatedData],
            [['property_1', 'property_2'], [$associatedData[0]]],
        ];
    }

    /**
     * @Given the entities get factored as association (not primary)
     * @And two entities have given properties
     * @When they share a unique property
     * @Then the second one will be ignored.
     *
     * @dataProvider dataForSanitizeEntityArrayOnAssociation
     * @param array $uniqueProperties
     * @param array $expectOutput
     */
    public function testSanitizeEntityArrayOnAssociation(array $uniqueProperties, array $expectOutput)
    {
        $factoryStub = $this->getMockBuilder(BaseFactory::class)->disableOriginalConstructor()->getMock();
        $factoryStub->method('getUniqueProperties')->willReturn($uniqueProperties);

        $associations = [
            ['property_1' => 'foo', 'property_2' => 'foo'],
            ['property_1' => 'foo', 'property_2' => 'dah'],
        ];

        $act = UniquenessJanitor::sanitizeEntityArray($factoryStub, $associations, false);

        $this->assertSame($expectOutput, $act);
    }
}