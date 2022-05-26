<?php

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
namespace TestPlugin\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Table;

class BillsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Article' => [
                    'className' => 'Articles'
                ],
                'Customer' => [
                    'className' => 'TestPlugin.Customers'
                ],
            ],
        ]);

        // Since the display field is an array, the injection of string in the
        // BillFactory is prohibited.
        $this->setDisplayField(['street', 'amount']);

        parent::initialize($config);
    }

    /**
     * @param Event $event
     * @param ArrayObject $data
     * @param ArrayObject $options
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        $data['beforeMarshalTriggeredPerDefault'] = true;
    }

    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $entity->set('afterSaveTriggeredPerDefault', true);
        $entity->set('created', '2010-01-01');
    }
}
