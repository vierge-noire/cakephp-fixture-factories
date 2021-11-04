<?php

declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Cat extends Entity
{
    protected $_accessible = [
        'name' => true,
        'country_id' => true,
        'country' => true,
    ];
}
