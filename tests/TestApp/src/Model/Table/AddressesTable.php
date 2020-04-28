<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class AddressesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasMany' => [
                'Author' => [
                    'className' => 'Authors'
                ],
            ],
            'belongsTo' => [
                'City' => [
                    'className' => 'Cities'
                ]
            ],
        ]);

        parent::initialize($config);
    }
}
