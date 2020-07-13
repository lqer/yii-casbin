<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Yiisoft\ActiveRecord\Tests\Stubs\Connection\MysqlConnection;
use Yiisoft\ActiveRecord\Tests\Stubs\Connection\PgsqlConnection;
use Yiisoft\ActiveRecord\Tests\Stubs\Connection\SqliteConnection;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Connection\ConnectionPool;
use Yiisoft\Db\Helper\Dsn;
use Yiisoft\Log\Target\File\FileRotator;
use Yiisoft\Log\Target\File\FileRotatorInterface;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Log\Logger;
use Yiisoft\Profiler\Profiler;
use lqer\Yii\Casbin\Permission;

return [
    Aliases::class => [
        '@root' => dirname(__DIR__, 1),
        '@fixtures' => '@root/tests/data/fixtures',
        '@runtime' => '@root/tests/data/runtime',
    ],

    CacheInterface::class => static function (ContainerInterface $container) {
        return new Cache(new ArrayCache());
    },

    FileRotatorInterface::class => static function () {
        return new FileRotator(10);
    },

    LoggerInterface::class => static function (ContainerInterface $container) {
        $aliases = $container->get(Aliases::class);
        $fileRotator = $container->get(FileRotatorInterface::class);

        $fileTarget = new FileTarget(
            $aliases->get('@runtime/logs/app.log'),
            $fileRotator
        );

        $fileTarget->setLevels(
            [
                LogLevel::EMERGENCY,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::INFO,
                LogLevel::DEBUG
            ]
        );

        return new Logger([
            'file' => $fileTarget,
        ]);
    },

    Profiler::class => static function (ContainerInterface $container) {
        return new Profiler($container->get(LoggerInterface::class));
    },

    Connection::class => static function (ContainerInterface $container) use ($params) {
        $cache = $container->get(CacheInterface::class);
        $logger = $container->get(LoggerInterface::class);
        $profiler = $container->get(Profiler::class);
        $config = $params['lqer/yii-casbin']['database'];
        $driver_name = $config['driver_name'];
        $dsn = new Dsn(
            $config[$driver_name]['dsn']['driver'],
            $config[$driver_name]['dsn']['host'],
            $config[$driver_name]['dsn']['dbname'],
            $config[$driver_name]['dsn']['port'],
        );
        $db = new Connection($cache, $logger, $profiler, $dsn->getDsn());
        $db->setUsername($config[$driver_name]['username']);
        $db->setPassword($config[$driver_name]['password']);
        ConnectionPool::setConnectionsPool($driver_name,$db);
        return $db;
    },

    Permission::class => static function (ContainerInterface $container) {
        return new Permission($container);
    },
];
