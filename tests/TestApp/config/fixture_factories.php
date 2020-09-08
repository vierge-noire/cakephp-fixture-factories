<?php

return [
    'TestFixtureTableSniffers' => [
        '\testDriver' => '\testTableSniffer'
    ],
    'TestFixtureIgnoredConnections' => [
        'test_dummy',
    ],
    'TestFixtureGlobalBehaviors' => [
        'SomeBehaviorUsedInMultipleTables',
    ],
];
