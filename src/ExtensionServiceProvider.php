<?php
/**
 * 作者:郭磊
 * 邮箱:174000902@qq.com
 * 电话:15210720528
 * Git:https://github.com/guolei19850528/laravel-tiehu
 */

namespace Guolei19850528\Laravel\Tiehu;

use Illuminate\Support\ServiceProvider;


/**
 * 扩展服务提供者
 */
class ExtensionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /**
         * 发布配置文件
         */
        $this->publishes([__DIR__ . '/../config/guolei19850528-laravel-tiehu.php' => config_path('guolei19850528-laravel-tiehu.php')], 'guolei19850528/laravel-tiehu');
    }
}
