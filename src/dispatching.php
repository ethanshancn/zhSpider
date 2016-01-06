<?php
/**
 * dispatching.php
 * Author: Ethan
 * CreateTime: 2016/1/6 20:53
 * Description: 线程调度器
 */
class dispatching
{
    //最多可同时进行的线程数
    private $maxThreadNum;

    //正在等待处理和正在处理中的人员名单,在检测人员是否已在名单中时必须先检测该名单然后再检测数据库
    private $waitAndDeal = array();

    private $pool = array();

    private static $instance;

    public static function getInstance($maxThreadNum = 0)
    {
        if(!(self::$instance instanceof self))
        {
            self::$instance = new self($maxThreadNum);
        }
        return self::$instance;
    }

    private function __construct($maxThreadNum = 0)
    {
        $this->maxThreadNum = ($maxThreadNum <= 0)?getConfig('maxThreadNum') : $maxThreadNum;
    }

    private function initPool()
    {
        for($i = 0; $i < $this->maxThreadNum; $i ++)
        {

        }
    }
}

class dealUser extends Thread
{
    public $userPageParam = array();

    public function run()
    {

    }
}