<?php

use lqer\Yii\Casbin\Adapter;

return [
    'aliases' => [
        '@root' => dirname(__DIR__),
        '@src' => '@root/src',
    ],

    'lqer/yii-casbin' => [
        'model' => [
            // Available Settings: "file", "text"
            'config_type' => 'file',

            'config_file_path' => dirname(__DIR__) .'/config/casbin-basic-model.conf',

            'config_text' => '',
        ],

        // Yii-casbin adapter .
        'adapter' => Adapter::class,

        /*
         * Yii-Casbin database setting.
         */
        'database' => [
            'driver_name' => 'mysql',
            // Database connection for following tables.
            'mysql' => [
                'dsn' => [
                    'driver' => 'mysql',
                    'host' => '192.168.0.151',
                    'dbname' => 'yii-demo',
                    'port' => '13306',
                ],
                'fixture' => dirname(__DIR__, 1) . '/vendor/yiisoft/db/tests/data/mysql.sql',
                'username' => 'default',
                'password' => 'secret',
            ],

            // CasbinRule tables and model.
            'casbin_rules_table' => '{{%casbin_rule}}',
        ],
    ],
];
