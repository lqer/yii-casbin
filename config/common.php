<?php

use App\Factory\AppRouterFactory;
use App\Factory\MailerFactory;
use App\Timer;
use Psr\Container\ContainerInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Group;
use Yiisoft\Router\RouteCollectorInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\UrlMatcherInterface;

/**
 * @var array $params
 */

return [
    ContainerInterface::class => static function (ContainerInterface $container) {
        return $container;
    },
];
