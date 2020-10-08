<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace TestApp\Model\Table;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

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

        $this->hasMany('Addresses');

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
     * @param ArrayObject $data
     * @param ArrayObject $options
     */
    public function beforeMarshal(Event $event, \ArrayObject $data, \ArrayObject $options)
    {
        $data['beforeMarshalTriggered'] = true;
    }
}
