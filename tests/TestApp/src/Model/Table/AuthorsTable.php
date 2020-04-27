<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config): void
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
            'hasMany' => [
                'Articles'
            ],
        ]);

        parent::initialize($config);
    }
}
