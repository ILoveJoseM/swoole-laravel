<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2020-04-23
 * Time: 22:55
 */

namespace JoseChan\SwooleLaravel\Providers;

use Illuminate\Support\ServiceProvider;
use JoseChan\Swoole\Utils\Listener;
use JoseChan\Swoole\Utils\Options;

class SwooleLaravelProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../bin/server.php' => public_path("server.php")], "swoole-laravel-server");
    }
}
