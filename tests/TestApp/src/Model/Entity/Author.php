<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * Author Entity
 *
 * @property int $id
 * @property string $name
 * @property int $address_id
 * @property int|null $business_address_id
 * @property string|null $biography
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Address $address
 * @property \App\Model\Entity\BusinessAddress $business_address
 * @property \App\Model\Entity\Article[] $articles
 */
class Author extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'name' => true,
        'address_id' => true,
        'business_address_id' => true,
        'biography' => true,
        'created' => true,
        'modified' => true,
        'address' => true,
        'business_address' => true,
        'articles' => true,
    ];
}
