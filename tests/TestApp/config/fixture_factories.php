<?php

return [
    'TestFixtureTruncators' => [
        '\testDriver' => '\testTruncator'
    ],
    'TestFixtureMigrations' => [
        ['connection' => 'test', 'source' => 'Migrations']
    ],
];
