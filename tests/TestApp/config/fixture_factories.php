<?php

return [
    'TestFixtureTruncators' => [
        '\testDriver' => '\testTruncator'
    ],
    'TestFixtureMigrations' => [
        ['connection' => 'test'],
        ['plugin' => 'TestPlugin']
    ],
];
