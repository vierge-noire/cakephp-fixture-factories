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
                'Article' => [
                    'className' => 'Articles'
                ],
                'Customer' => [
                    'className' => 'TestPlugin.Customers'
                ],
            ],
        ]);

        parent::initialize($config);
    }
}
