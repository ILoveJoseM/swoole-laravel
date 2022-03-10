<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-03
 * Time: 18:16
 */

namespace JoseChan\SwooleLaravel\Server;


use App\Http\Kernel;
use Illuminate\Foundation\Application;
use JoseChan\Swoole\Utils\HttpServer;
use JoseChan\Swoole\Utils\Traits\ServerTrait;
use JoseChan\SwooleLaravel\Utils\AsyncCo;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server;

class LaravelServer extends HttpServer
{
    use ServerTrait;

    /** @var Application $app */
    private $app;
    /** @var \App\Http\Kernel $kernel */
    private $kernel;

    /** @var Response */
    private $response = null;


    protected function onWorkerStart(Server $server, int $workerId)
    {
        // 这个用来初始化laravel框架
        if (!$server->taskworker) {
            // 开启自动协程
//            \Swoole\Coroutine::set(["hook_flags" => SWOOLE_HOOK_ALL|SWOOLE_HOOK_CURL, "enable_preemptive_scheduler" => true]);
            $this->app = require_once ROOT_PATH . 'bootstrap/app.php';
            $this->kernel = $this->app->make(Kernel::class);
        }
    }

    protected function onRequest(Request $request, Response $response)
    {
        // 记录当前worker需要处理的的response
//        $this->response = $response;
        $_GET = empty($request->get) ? [] : $request->get;
        $_POST = empty($request->post) ? [] : $request->post;
        $_REQUEST = array_merge($_GET, $_POST);
        $_COOKIE = empty($request->cookie) ? [] : $request->cookie;
        $_FILES = empty($request->files) ? [] : $request->files;
        $_SERVER = [];
        foreach ($request->server as $key => $value) {
            $key = strtoupper($key);
            $_SERVER[$key] = $value;
        }
        /** @var \Illuminate\Http\Response $laravel_response */
        $laravel_response = $this->kernel->handle($laravel_request = \Illuminate\Http\Request::capture());
        foreach ($laravel_response->headers as $key => $header) {
            foreach ($header as $value) {
                $response->header($key, $value);
            }
        }

//        $response->end("A");
        $response->end($laravel_response->content());

        $this->kernel->terminate($laravel_request, $laravel_response);
//        $this->response = null;// 销毁

        return;
    }
}
