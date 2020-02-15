<?php

namespace TestApp\Model\Table;

use Cake\ORM\Table;

class EntitiesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

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

}
