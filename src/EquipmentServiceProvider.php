<?php

namespace Hsvisus\Equipment;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class EquipmentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * 服务提供者加是否延迟加载.
     * @var bool
     */
    protected $defer = true; // 延迟加载服务
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hardware', function ($app) {
            return new Equipment();

        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        //路由
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        //添加中间件
        //$this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(CheckDeviceMiddleware::class);
        //$this->addMiddlewareAlias('admittance', CheckDeviceMiddleware::class);
        //配置文件
        $this->publishes([
            __DIR__.'/Config/equipment.php' => config_path('equipment.php'),
        ]);
        //数据迁移
        $migrations = [
            __DIR__.'/Migrations/2021_07_08_085150_create_manufacture_table.php',
            __DIR__.'/Migrations/2021_07_12_100025_create_equipment_persons_table.php',
            __DIR__.'/Migrations/2021_07_12_100026_create_task_table.php',
            __DIR__.'/Migrations/2021_07_14_104405_create_task_history_table.php',
            __DIR__.'/Migrations/2021_07_15_091536_create_faces_table.php',
        ];
        $this->loadMigrationsFrom($migrations);
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['hardware'];
    }

    /**
     * @param $name
     * @param $class
     * @return mixed
     */
    protected function addMiddlewareAlias($name, $class)
    {
        $router = $this->app['router'];
        // 判断aliasMiddleware是否在类中存在
        if (method_exists($router, 'aliasMiddleware')) {
            // aliasMiddleware 顾名思义,就是给中间件设置一个别名
            return $router->aliasMiddleware($name, $class);
        }
        return $router->middleware($name, $class);
    }
}
