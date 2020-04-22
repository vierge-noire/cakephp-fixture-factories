<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use TestApp\Model\Entity\Author;

class CountriesTable extends Table
{
    public function initialize(array $config)
    {
        $this->setEntityClass(Author::class);

        $this->addAssociations([
            'belongsTo' => [
                'Continent' => [
                    'className' => 'Options',
                    'foreignkey' => 'continent_id'
                ]
            ]
        ]);

        parent::initialize($config);
    }
}
