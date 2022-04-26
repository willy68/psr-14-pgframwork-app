<?php

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration',
        'version_column_name' => 'version',
        'version_column_length' => 1024,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],

    'migrations_paths' => [
        'App\Api\db\migrations' => './app/Api/db/migrations',
    ],

    'all_or_nothing' => true,
    'check_database_platform' => true,
    'organize_migrations' => 'none',
    'connection' => 'paysagest',
    'em' => 'paysagest',
];
