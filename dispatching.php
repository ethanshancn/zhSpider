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

    private $pool = array();

    /*
     * 正在等待处理和正在处理中的人员名单,在检测人员是否已在名单中时必须先检测该名单然后再检测数据库
     * 数组索引为sHashId,
     * 值为需要传入userPage的参数
     */
    private static $waiting = array();

    private static $handlingAndComplete = array();

    private static $instance;

    public static function getInstance($maxThreadNum = 0)
    {
        if(!(self::$instance instanceof self))
        {
            self::$instance = new self($maxThreadNum);
        }
        return self::$instance;
    }

    public static function getNextUser()
    {
        if(!(self::$instance instanceof self))
        {
            self::getInstance();
        }
        if(count(self::$waiting) > 0)
        {
            $tmp = array_shift(self::$waiting);
            self::$handlingAndComplete[$tmp['hashId']] = TRUE;
            return $tmp;
        }
        else
        {
            return array();
        }
    }

    public function addUser($userHashId, $param)
    {
        if(!$userHashId || !$param['url'])
        {
            //记录日志

            return FALSE;
        }
        if(!isset(self::$waiting[$userHashId]) && !isset(self::$handlingAndComplete[$userHashId]))
        {
            self::$waiting[$userHashId] = $param;

            print_r(self::$waiting);

        }
    }

    private function __construct($maxThreadNum = 0)
    {
        $this->maxThreadNum = ($maxThreadNum <= 0)?getConfig('maxThreadNum') : $maxThreadNum;
        $this->initPool();
    }

    private function initPool()
    {
        for($i = 0; $i < $this->maxThreadNum; $i ++)
        {
            $this->pool[$i] = new handleUser();
            $this->pool[$i]->start();
            //每隔50毫秒初始化一个线程
            usleep(50);
        }
    }
}

class handleUser extends Thread
{
    public $userPageParam = array();

    public function run()
    {
        while(1)
        {
            //若为空则尝试自动获取
            if(empty($this->userPageParam) || !isset($this->userPageParam['url']) || empty($this->userPageParam['url']))
            {
                $this->userPageParam = dispatching::getNextUser();
            }
            if(!empty($this->userPageParam))
            {
                loadClass('userPage',$this->userPageParam);
                $this->userPageParam = array();
            }
            sleep(1);
        }
    }
}