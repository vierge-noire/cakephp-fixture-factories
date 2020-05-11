<?php

return [
    Mysql::class => \CakephpFixtureFactories\TestSuite\MySQLTruncator::class,
    Sqlite::class => \CakephpFixtureFactories\TestSuite\SqliteTruncator::class
];
