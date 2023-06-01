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
 * @property string|null $json_field
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
     * @inheritdoc
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
