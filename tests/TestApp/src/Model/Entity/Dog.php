<?php

declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Dog extends Entity
{
    protected $_accessible = [
        'name' => true,
        'country_id' => true,
        'country' => true,
    ];
}
