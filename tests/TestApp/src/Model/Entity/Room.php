<?php

declare(strict_types=1);

namespace TestApp\Model\Entity;

use Cake\ORM\Entity;

class Room extends Entity
{
    protected $_accessible = [
        'name' => true,
        'address_id' => true,
        'cat_id' => true,
        'dog_id' => true,
        'address' => true,
        'cat' => true,
        'dog' => true,
    ];
}
