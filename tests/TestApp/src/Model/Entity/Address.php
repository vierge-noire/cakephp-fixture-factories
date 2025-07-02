<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * Address Entity
 *
 * @property int $id
 * @property string $street
 * @property int $city_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \TestApp\Model\Entity\City $city
 * @property \TestApp\Model\Entity\Author[] $authors
 */
class Address extends Entity
{
    /**
     * @inheritdoc
     */
    protected array $_accessible = [
        'street' => true,
        'city_id' => true,
        'created' => true,
        'modified' => true,
        'city' => true,
        'authors' => true,
    ];
}
