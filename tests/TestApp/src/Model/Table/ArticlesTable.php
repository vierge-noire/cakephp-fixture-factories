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
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Sluggable');
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasMany' => [
                'TestPlugin.Bills',
            ],
            'belongsToMany' => [
                'Authors',
                'ExclusivePremiumAuthors' => [
                    'foreignKey' => 'article_id',
                    'className' => 'PremiumAuthors',
                    'targetForeignKey' => 'author_id',
                    'joinTable' => 'articles_authors',
                    'propertyName' => PremiumAuthorsTable::ASSOCIATION_ALIAS,
                ],
            ],
        ]);
        parent::initialize($config);
    }
}
