<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class CitiesTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Country' => [
                    'className' => 'Countries'
                ],
            ],
        ]);

        parent::initialize($config);
    }
}
