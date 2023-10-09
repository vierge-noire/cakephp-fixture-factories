<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * City Entity
 *
 * @property int $id
 * @property string $name
 * @property int $country_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property string $virtual_unique_stamp
 *
 * @property \TestApp\Model\Entity\Country $country
 * @property \TestApp\Model\Entity\Address[] $addresses
 */
class City extends Entity
{
    /**
     * @inheritdoc
     */
    protected array $_accessible = [
        'name' => true,
        'country_id' => true,
        'created' => true,
        'modified' => true,
        'country' => true,
        'addresses' => true,
    ];
}
