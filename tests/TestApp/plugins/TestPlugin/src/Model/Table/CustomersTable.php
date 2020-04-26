<?php

namespace TestPlugin\Model\Table;

use Cake\ORM\Table;

class CustomersTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Address' => [
                    'className' => 'Addresses'
                ],
            ],
            'hasMany' => [
                'TestPlugin.Bills',
            ],
        ]);

        parent::initialize($config);
    }
}
