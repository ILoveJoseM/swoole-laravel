# swoole-laravel

## 安装

````ssh
composer require "jose-chan/swoole-laravel" -vvv
````

## laravel发布

````ssh
php artisan vendor:publish --tag=swoole-laravel-server
````

## 修改配置

在public/server.php修改端口及配置

## 启动

````
php public/server.php 
````

## 其他使用

#### 协程组件

````php
<?php
class TestController extends \App\Http\Controllers\Controller
{
    public function fetch(\Illuminate\Http\Request $request)
    {
        // 创建协程
        $coroutine1 = \JoseChan\SwooleLaravel\Utils\AsyncCo::coroutine(function () {
            $c = microtime(true);

            echo "开始查询1\n";
            $orders = \Illuminate\Database\Eloquent\Model::query()->get();
            $d = microtime(true);
            echo "查询1结束", ($d - $c), "\n";
            return $orders;
        });

        $coroutine2 = \JoseChan\SwooleLaravel\Utils\AsyncCo::coroutine(function () {
            $e = microtime(true);
            echo "开始查询2\n";
            $orders = \Illuminate\Database\Eloquent\Model::query()->get();
            $f = microtime(true);
            echo "查询2结束", ($f - $e), "\n";
            return $orders;
        });
        
        // 等待协程执行完成
        $coroutine1->join();
        // 等待协程执行完成，超时跳过等待，等待1s(1000ms)
        $coroutine2->join(1000);
    }
}
````
