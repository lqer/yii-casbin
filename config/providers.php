<?php

declare(strict_types=1);

use lqer\Yii\Casbin\Provider\DbProvider;
use Yiisoft\Arrays\Modifier\ReverseBlockMerge;

return [
    'dbProvider' => DbProvider::class,
    ReverseBlockMerge::class => new ReverseBlockMerge(),
];
