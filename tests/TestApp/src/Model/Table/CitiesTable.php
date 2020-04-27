<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class CitiesTable extends Table
{
    public function initialize(array $config): void
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
