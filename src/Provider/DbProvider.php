<?php

declare(strict_types=1);

namespace lqer\Yii\Casbin\Provider;

use Psr\Container\ContainerInterface;
use Yiisoft\Di\Support\ServiceProvider;
use Yiisoft\Db\Connection\Connection;

final class DbProvider extends ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        $container->get(Connection::class);
    }
}
