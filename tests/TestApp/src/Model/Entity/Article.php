<?php
declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * @property int $id
 * @property string $title
 * @property string|null $body
 * @property int $published
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \TestPlugin\Model\Entity\Bill[] $bills
 * @property \TestApp\Model\Entity\Author[] $authors
 * @property \Cake\ORM\Entity[] $articles_authors
 */
class Article extends Entity
{
    public const HIDDEN_PARAGRAPH_PROPERTY_NAME = 'hidden_paragraph';

    /**
     * @inheritdoc
     */
    protected $_accessible = [
        'title' => true,
        'body' => true,
        'published' => true,
        'created' => true,
        'modified' => true,
        'bills' => true,
        'authors' => true,
    ];

    protected $_hidden = [
        self::HIDDEN_PARAGRAPH_PROPERTY_NAME,
    ];
}
