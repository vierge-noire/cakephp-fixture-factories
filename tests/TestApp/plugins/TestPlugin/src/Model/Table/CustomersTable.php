<?php

namespace TestPlugin\Model\Table;

use Cake\ORM\Table;

class CustomersTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasMany' => [
                'TestPlugin.Bills',
            ],
        ]);

        parent::initialize($config);
    }
}
