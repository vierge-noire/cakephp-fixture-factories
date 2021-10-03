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
namespace TestPlugin\Model\Entity;

use Cake\ORM\Entity as BaseEntity;

/**
 * Bill Entity
 *
 * @property int $id
 * @property string $street
 * @property int $customer_id
 * @property int $article_id
 * @property int $amount
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \TestPlugin\Model\Entity\Customer $customer
 * @property \TestApp\Model\Entity\Article $article
 */
class Bill extends BaseEntity
{
    protected $_accessible = [
        'customer_id' => true,
        'article_id' => true,
        'amount' => true,
        'created' => true,
        'modified' => true,
        'customer' => true,
        'article' => true,
    ];
}
