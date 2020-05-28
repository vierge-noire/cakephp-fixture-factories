<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Address' => [
                    'className' => 'Addresses'
                ],
                'BusinessAddress' => [
                    'className' => 'Addresses'
                ],
            ],
            'belongsToMany' => [
                'Articles'
            ],
        ]);

        parent::initialize($config);
    }
}
