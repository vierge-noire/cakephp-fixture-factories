<?php
declare(strict_types=1);

namespace TestPlugin\Model\Table;

use Cake\ORM\Table;

class CustomersTable extends Table
{
    public function initialize(array $config): void
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
