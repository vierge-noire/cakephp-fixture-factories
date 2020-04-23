<?php

namespace TestPlugin\Model\Table;

use Cake\ORM\Table;

class BillsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Customer' => [
                    'className' => 'TestPlugin.Customers'
                ],
                'Article' => [
                    'className' => 'Articles'
                ]
            ],
        ]);
        parent::initialize($config);
    }
}
