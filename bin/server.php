#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-03
 * Time: 15:52
 */

define("ROOT_PATH", dirname(__DIR__) . "/");
require ROOT_PATH . "vendor/autoload.php";


\Swoole\Coroutine::set(["hook_flags" => SWOOLE_HOOK_ALL|SWOOLE_HOOK_CURL, "enable_preemptive_scheduler" => true]);
$options = new \JoseChan\Swoole\Utils\Options();
$options->worker_num = 10;
$options->max_request = 10;
$options->enable_coroutine = true;
//$options->daemonize = 1;
//$options->task_worker_num = 2;

$listener = new \JoseChan\Swoole\Utils\Listener("127.0.0.1", 8100);
$event = new \JoseChan\Swoole\Utils\Events();

$server = new \JoseChan\SwooleLaravel\Server\LaravelServer($options, $listener, $event);
$server->start();
