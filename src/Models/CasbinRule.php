<?php

namespace lqer\Yii\Casbin\models;

use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Composer\Config\Builder;

class CasbinRule extends ActiveRecord
{
    protected static ?Connection $db = null;

    /**
     * @return string Active Record
     */
    public static function tableName(): string
    {
        $config = require Builder::path('params');
        if (isset($config['lqer/yii-casbin']['database']['casbin_rules_table'])) {
            return $config['lqer/yii-casbin']['database']['casbin_rules_table'];
        }
        return parent::tableName();
    }

    /**
     * getDb.
     *
     * @return yii\db\Connection
     */
    public static function getDb(): Connection
    {
        if (static::$db) {
            return static::$db;
        }

        $config = require Builder::path('params');
        if (isset($config['lqer/yii-casbin']['database']['driver_name'])) {
            static::connectionId($config['lqer/yii-casbin']['database']['driver_name']);
        }
        return static::getConnection();
    }

    /**
     * Calls the named method which is not a class method.
     *
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     *
     * @param string $name   the method name
     * @param array  $params method parameters
     *
     * @return mixed the method return value
     *
     * @throws UnknownMethodException when calling unknown method
     */
    public function __call($name, $params)
    {
        return call_user_func_array([static::getDb(), $name], $params);
    }
}
