<?php
declare(strict_types=1);

namespace TestApp\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CountriesTable extends Table
{
    const NAME_MAX_LENGTH = 100;

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');

        $this->addAssociations([
            'hasMany' => [
                'Cities',
            ],
        ]);

        parent::initialize($config);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator->maxLength('name', self::NAME_MAX_LENGTH);

        return $validator;
    }
}
