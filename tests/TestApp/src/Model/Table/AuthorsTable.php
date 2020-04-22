<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasOne' => [
                'Address' => [
                    'className' => 'Addresses'
                ],
            ],
            'hasMany' => [
                'Articles'
            ],
        ]);

        parent::initialize($config);
    }
}
