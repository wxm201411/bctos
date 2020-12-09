<?php
$config = require "config/database.php";
$config = $config['connections']['mysql'];
return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => $config['prefix'].'phinxlog',
            'default_environment' => 'development',
            'development' => [
                'adapter' => $config['type'],
                'host' => $config['hostname'],
                'name' => $config['database'],
                'user' => $config['username'],
                'pass' => $config['password'],
                'port' => $config['hostport'],
                'charset' => $config['charset']
            ]
        ],
        'version_order' => 'creation'
    ];
