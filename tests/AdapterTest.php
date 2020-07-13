<?php

namespace lqer\Yii\Casbin\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Di\Container;
use Yiisoft\Composer\Config\Builder;
use lqer\Yii\Casbin\models\CasbinRule;
use lqer\Yii\Casbin\Permission;
use Psr\Container\ContainerInterface;

class AdapterTest extends TestCase
{
    protected ContainerInterface $container;
    protected ?Permission $permission = null;
    protected ?Connection $connection = null;

    public function testEnforce(): void
    {
        $this->assertTrue($this->permission->enforce('alice', 'data1', 'read'));

        $this->assertFalse($this->permission->enforce('bob', 'data1', 'read'));
        $this->assertTrue($this->permission->enforce('bob', 'data2', 'write'));

        $this->assertTrue($this->permission->enforce('alice', 'data2', 'read'));
        $this->assertTrue($this->permission->enforce('alice', 'data2', 'write'));
    }

    public function testAddPolicy(): void
    {
        $this->assertFalse($this->permission->enforce('eve', 'data3', 'read'));
        $this->permission->addPermissionForUser('eve', 'data3', 'read');
        $this->assertTrue($this->permission->enforce('eve', 'data3', 'read'));
    }

    public function testSavePolicy(): void
    {
        $this->assertFalse($this->permission->enforce('alice', 'data4', 'read'));

        $model = $this->permission->getModel();
        $model->clearPolicy();
        $model->addPolicy('p', 'p', ['alice', 'data4', 'read']);

        $adapter = $this->permission->getAdapter();
        $adapter->savePolicy($model);
        $this->assertTrue($this->permission->enforce('alice', 'data4', 'read'));
    }

    public function testRemovePolicy(): void
    {
        $this->assertFalse($this->permission->enforce('alice', 'data5', 'read'));

        $this->permission->addPermissionForUser('alice', 'data5', 'read');
        $this->assertTrue($this->permission->enforce('alice', 'data5', 'read'));

        $this->permission->deletePermissionForUser('alice', 'data5', 'read');
        $this->assertFalse($this->permission->enforce('alice', 'data5', 'read'));
    }

    public function testRemoveFilteredPolicy(): void
    {
        $this->assertTrue($this->permission->enforce('alice', 'data1', 'read'));
        $this->permission->removeFilteredPolicy(1, 'data1');
        $this->assertFalse($this->permission->enforce('alice', 'data1', 'read'));
        $this->assertTrue($this->permission->enforce('bob', 'data2', 'write'));
        $this->assertTrue($this->permission->enforce('alice', 'data2', 'read'));
        $this->assertTrue($this->permission->enforce('alice', 'data2', 'write'));
        $this->permission->removeFilteredPolicy(1, 'data2', 'read');
        $this->assertTrue($this->permission->enforce('bob', 'data2', 'write'));
        $this->assertFalse($this->permission->enforce('alice', 'data2', 'read'));
        $this->assertTrue($this->permission->enforce('alice', 'data2', 'write'));
        $this->permission->removeFilteredPolicy(2, 'write');
        $this->assertFalse($this->permission->enforce('bob', 'data2', 'write'));
        $this->assertFalse($this->permission->enforce('alice', 'data2', 'write'));
    }

    /**
     * init table.
     */
    protected function initTable(): void
    {
        $db = CasbinRule::getDb();
        $tableName = CasbinRule::tableName();
        $table = $db->getTableSchema($tableName);
        if ($table) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $this->permission->init();

        $db->createCommand()->batchInsert(
            $tableName,
            ['ptype', 'v0', 'v1', 'v2'],
            [
                ['p', 'alice', 'data1', 'read'],
                ['p', 'bob', 'data2', 'write'],
                ['p', 'data2_admin', 'data2', 'read'],
                ['p', 'data2_admin', 'data2', 'write'],
                ['g', 'alice', 'data2_admin', null],
            ]
        )->execute();
    }

    /**
     * Refresh the application instance.
     */
    protected function refreshApplication(): void
    {
        // Builder::rebuild();

        $container = new Container(
            require Builder::path('tests'),
            require Builder::path('providers'),
        );
        $this->container = $container;
        $this->permission = $this->container->get(Permission::class);
    }

    /**
     * This method is called before each test.
     */
    protected function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        if (!$this->permission) {
            $this->refreshApplication();
        }

        $this->initTable();
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        unset($this->container, $this->permission);

        parent::tearDown();
    }
}
