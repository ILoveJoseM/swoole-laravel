<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-03
 * Time: 17:30
 */

namespace JoseChan\SwooleLaravel\Utils;


use Swoole\Coroutine;
use JoseChan\SwooleLaravel\Utils\Coroutine as JcCoroutine;

class AsyncCo
{
    /**
     * 创建协程，返回协程对象
     * @param \Closure $task
     * @return \JoseChan\SwooleLaravel\Utils\Coroutine
     */
    public static function coroutine(\Closure $task)
    {
        $coroutine = new JcCoroutine(0, JcCoroutine::STATUS_CREATED);
        if (self::isCoroutine()) {
            // 协程环境
            $id = Coroutine::create(function () use ($task, $coroutine) {
                try {
                    $result = $task();
                    $coroutine->setResult($result);
                    $coroutine->setStatus(JcCoroutine::STATUS_FINISH);
                } catch (\Throwable $throwable) {
                    $coroutine->setException($throwable);
                    $coroutine->setStatus(JcCoroutine::STATUS_FAIL);
                }
            });

        } else {
            // 非协程环境，直接执行
            try {
                $result = $task();
                $coroutine->setResult($result);
                $coroutine->setStatus(JcCoroutine::STATUS_FINISH);
            } catch (\Throwable $throwable) {
                $coroutine->setException($throwable);
                $coroutine->setStatus(JcCoroutine::STATUS_FAIL);
            }
            $id = -1;
        }
        $coroutine->setId($id);
        return $coroutine;
    }

    /**
     * 等待所有协程执行结束
     * @param int $timeout millisecond
     */
    public static function wait($timeout = 0)
    {
        if (!self::isCoroutine()) {
            // 非协程环境，不阻塞
            return;
        }
        $start = microtime(true);

        if ($timeout > 0) {
            $time_left = $timeout;
        }

        do {
            $status = Coroutine::stats();
            if (isset($time_left) && $time_left <= 0) {
                break;
            }

            if (isset($time_left)) {
                $now = microtime(true);
                $sleep = ($now - $start) * 1000;
                $time_left -= $sleep;
            }
        } while ($status['coroutine_num'] > 1);
    }

    /**
     * 判断当前是否协程环境
     * @return bool
     */
    public static function isCoroutine()
    {
        return class_exists(Coroutine::class) && Coroutine::getuid() != -1;
    }
}
