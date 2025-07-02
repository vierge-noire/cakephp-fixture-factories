<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;
use CakephpFixtureFactories\Test\TestCase\Factory\BaseFactoryAssociationsTest;

/**
 * Country Entity
 *
 * @property int $id
 * @property string $name
 * @property string $unique_stamp
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \TestApp\Model\Entity\City[] $cities
 * @property bool $virtual_cities
 */
class Country extends Entity
{
    /**
     * @inheritdoc
     */
    protected array $_accessible = [
        'name' => true,
        'created' => true,
        'modified' => true,
        'cities' => true,
        'beforeMarshalTriggered' => true,
        'eventApplied' => true,
    ];

    /**
     * @see BaseFactoryAssociationsTest::testAssociationWithVirtualFieldNamedIdentically()
     * @return false
     */
    protected function _getVirtualCities(): bool
    {
        return false;
    }
}
