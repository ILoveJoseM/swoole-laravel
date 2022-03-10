<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-03
 * Time: 19:12
 */

namespace JoseChan\SwooleLaravel\Utils;


class Coroutine
{

    const STATUS_CREATED = 1;
    const STATUS_FINISH = 2;
    const STATUS_FAIL = 3;

    static $finish = [self::STATUS_FINISH, self::STATUS_FAIL];

    /** @var int $id 协程id */
    private $id;
    /** @var int $status 协程状态 */
    private $status;
    /** @var mixed $result 协程式执行的返回值 */
    private $result;

    /** @var \Throwable $exception 异常 */
    private $exception;

    /**
     * Coroutine constructor.
     * @param $id
     * @param $status
     * @param $result
     */
    public function __construct($id, $status, $result = null)
    {
        $this->id = $id;
        $this->status = $status;
        $this->result = $result;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }


    /**
     * @param int $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }

    /**
     * @param \Throwable $exception
     */
    public function setException(\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 等待协程结束，并且获取结果
     * @param int $timeout millisecond
     * @return mixed|null
     * @throws \Throwable
     */
    public function join($timeout = 0)
    {
        $start = microtime(true);
        if ($timeout > 0) {
            $time_left = $timeout;
        }

        do {
            if (isset($time_left) && $time_left <= 0) {
                break;
            }

            if (isset($time_left)) {
                $now = microtime(true);
                $sleep = ($now - $start) * 1000;
                $time_left -= $sleep;
            }
        } while (!in_array($this->status, self::$finish));

        if ($this->status == self::STATUS_FINISH) {
            return $this->result;
        } else {
            throw $this->exception;
        }
    }
}
