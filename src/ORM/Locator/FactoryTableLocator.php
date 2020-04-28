<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\ORM\Locator;

use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Table;

class FactoryTableLocator extends TableLocator
{
    protected function _create(array $options): Table
    {
        $cloneTable = parent::_create($options);
        $ormEvents = [
            'Model.initialize',
            'Model.beforeMarshal',
            'Model.beforeFind',
            'Model.buildValidator',
            'Model.buildRules',
            'Model.beforeRules',
            'Model.afterRules',
            'Model.beforeSave',
            'Model.afterSave',
            'Model.afterSaveCommit',
            'Model.beforeDelete',
            'Model.afterDelete',
            'Model.afterDeleteCommit',
        ];

        foreach ($cloneTable->behaviors()->loaded() as $behaviorName) {
            if ($behaviorName === 'Timestamp') {
                continue;
            }
            $cloneTable->removeBehavior($behaviorName);
        }

        foreach ($ormEvents as $ormEvent) {
            foreach ($cloneTable->getEventManager()->listeners($ormEvent) as $listeners) {
                if (array_key_exists('callable', $listeners) && is_array($listeners['callable'])) {
                    if ($listeners['callable'][0] instanceof TimestampBehavior) {
                        continue;
                    }
                }
                $cloneTable->getEventManager()->off($ormEvent, $listeners['callable']);
            }
        }

        return $cloneTable;
    }
}
