<?php

return [
    'TestFixtureTableSniffers' => [
        '\testDriver' => '\testTableSniffer'
    ],
    'TestFixtureMigrations' => [
        ['connection' => 'test'],
        ['plugin' => 'TestPlugin']
    ],
];
