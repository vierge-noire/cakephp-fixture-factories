<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class ProjectsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addAssociations([
            'hasOne' => [
                'Address' => [
                    'className' => 'Addresses'
                ]
            ],
            'belongsToMany' => [
                'Entity' => [
                    'className' => 'Entities'
                ],
            ],
            'hasMany' => [
                'Tags' => [
                    'className' => 'Options'
                ]
            ]
        ]);
    }

}
