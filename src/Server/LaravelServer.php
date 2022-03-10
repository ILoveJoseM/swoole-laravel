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

/**
 * http服务器
 * Class LaravelServer
 * @package JoseChan\SwooleLaravel\Server
 */
class LaravelServer extends HttpServer
{
    use ServerTrait;

    /** @var Application $app */
    private $app;
    /** @var \App\Http\Kernel $kernel */
    private $kernel;

    /**
     * worker进程启动回调
     * worker启动时，初始化laravel框架
     * @param Server $server
     * @param int $workerId
     */
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

    /**
     * 接收http请求
     * 处理将swoole的http请求对象映射成laravel的http请求对象
     * 并且将laravel的http响应对象，通过swoole的http响应对象发出去
     * @param Request $request
     * @param Response $response
     * @return mixed|void
     */
    protected function onRequest(Request $request, Response $response)
    {
        // 记录当前worker需要处理的的response
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

        $response->end($laravel_response->content());

        $this->kernel->terminate($laravel_request, $laravel_response);

        return;
    }
}
