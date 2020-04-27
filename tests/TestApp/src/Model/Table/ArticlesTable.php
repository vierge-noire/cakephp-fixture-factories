<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasMany' => [
                'TestPlugin.Bills',
            ],
            'belongsToMany' => [
                'Authors'
            ],
        ]);
        parent::initialize($config);
    }
}
