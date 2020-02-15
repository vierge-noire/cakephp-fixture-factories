<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;
use TestApp\Model\Entity\Address;

class AddressesTable extends Table
{
    public function initialize(array $config)
    {
        $this->setEntityClass(Address::class);

        $this->addAssociations([
            'belongsTo' => [
                'Country' => [
                    'className' => 'Countries',
                    'foreignKey' => 'country_id',
                ]
            ]
        ]);
        parent::initialize($config);
    }
}
