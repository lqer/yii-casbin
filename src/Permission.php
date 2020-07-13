<?php

namespace lqer\Yii\Casbin;

use Psr\Container\ContainerInterface;
use Casbin\Model\Model;
use Casbin\Enforcer;
use Yiisoft\Composer\Config\Builder;
use lqer\Yii\Casbin\models\CasbinRule;

/**
 * Permission.
 *
 * @author lqer@qq.com
 */
class Permission
{
    public $enforcer;

    public $adapter;

    public $model;

    public $config = [];

    private ?ContainerInterface $container = null;
    public function __construct(ContainerInterface $container)
    {
        $config = require Builder::path('params');
        $this->config = $config['lqer/yii-casbin'];

        $this->adapter = $container->get($this->config['adapter']);

        $this->model = new Model();
        if ('file' == $this->config['model']['config_type']) {
            $this->model->loadModel($this->config['model']['config_file_path']);
        } elseif ('text' == $this->config['model']['config_type']) {
            $this->model->loadModelFromText($this->config['model']['config_text']);
        }
    }

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        $db = CasbinRule::getDb();
        $tableName = CasbinRule::tableName();
        $table = $db->getTableSchema($tableName);
        if (!$table) {
            $res = $db->createCommand()->createTable($tableName, [
                'id' => 'pk',
                'ptype' => 'string',
                'v0' => 'string',
                'v1' => 'string',
                'v2' => 'string',
                'v3' => 'string',
                'v4' => 'string',
                'v5' => 'string',
            ])->execute();
        }
    }

    public function enforcer($newInstance = false)
    {
        if ($newInstance || is_null($this->enforcer)) {
            $this->init();
            $this->enforcer = new Enforcer($this->model, $this->adapter);
        }

        return $this->enforcer;
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
        return call_user_func_array([$this->enforcer(), $name], $params);
    }
}
