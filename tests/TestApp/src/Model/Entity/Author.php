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
 * @property \TestApp\Model\Entity\Address $address
 * @property \TestApp\Model\Entity\Address|string[] $business_address
 * @property \TestApp\Model\Entity\Article[] $articles
 */
class Author extends Entity
{
    public const SET_FIELD_PREFIX = 'set_field_prefix_';
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = ['*' => false];

    protected function _setFieldWithSetter_1(string $value): string
    {
        return $this->prependPrefixToField($value);
    }

    protected function _setFieldWithSetter_2(string $value): string
    {
        return $this->prependPrefixToField($value);
    }

    protected function _setFieldWithSetter_3(string $value): string
    {
        return $this->prependPrefixToField($value);
    }

    public function prependPrefixToField(string $value): string
    {
        return self::SET_FIELD_PREFIX . $value;
    }
}
