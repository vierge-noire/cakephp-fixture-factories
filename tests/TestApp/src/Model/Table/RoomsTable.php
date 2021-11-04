<?php

declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class RoomsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->belongsTo('Addresses');
        $this->belongsTo('Cats');
        $this->belongsTo('Dogs');
        parent::initialize($config);
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->requirePresence('country_id', 'create')
            ->notEmptyString('country_id');
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules->add(function ($entity, $options) {
            return false;
        }, 'someRuleToSkip');
    }

    /**
     * @param Event $event
     * @param \ArrayObject $data
     * @param \ArrayObject $options
     */
    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $data['beforeMarshalTriggered'] = true;
    }
}
