<?php

namespace TestApp\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\Table;

class EntitiesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'belongsTo' => [
                'Address' => [
                    'className' => 'Addresses',
                    'foreignKey' => 'address_id',
                ],
                'EntityType' => [
                    'className' => 'Options',
                    'foreignKey' => 'entity_type_id',
                ],
            ],
            'belongsToMany' => [
                'Project' => [
                    'className' => 'Projects',
                    'joinTable' => 'entities_projects',
                    'foreignKey' => 'entity_id',
                    'targetForeignKey' => 'project_id',
                    'unique' => 'keepExisting',
                    'sort' => 'Project.name',
                ]
            ]
        ]);
    }

    public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary)
    {
        return $event;
    }
}
