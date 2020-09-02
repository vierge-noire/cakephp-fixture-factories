<?php

return [
    'TestFixtureTableSniffers' => [
        '\testDriver' => '\testTableSniffer'
    ],
    'TestFixtureMigrations' => [
        ['connection' => 'test'],
        ['plugin' => 'TestPlugin']
    ],
    'TestFixtureIgnoredConnections' => [
        'test_dummy',
    ],
    'TestFixtureGlobalBehaviors' => [
        'SomeBehaviorUsedInMultipleTables',
    ],
];
