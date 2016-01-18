<?php
/**
 * dispatching.php
 * Author: Ethan
 * CreateTime: 2016/1/6 20:53
 * Description: 线程调度器
 */

/*
 * 正在等待处理和正在处理中的人员名单,在检测人员是否已在名单中时必须先检测该名单然后再检测数据库
 * 数组索引为sHashId,
 * 值为需要传入userPage的参数
 */
$waiting = array();

$handlingAndComplete = array();

class dispatching
{
    private static $pool = array();

    /*
     * 正在等待处理和正在处理中的人员名单,在检测人员是否已在名单中时必须先检测该名单然后再检测数据库
     * 数组索引为sHashId,
     * 值为需要传入userPage的参数
     */

    public static function getNextUser()
    {

        echo 'DELETE '.count($GLOBALS['waiting'])."\n";

        return array();

        /*if(count($GLOBALS['waiting']) > 0)
        {
            $tmp = array_shift($GLOBALS['waiting']);
            $GLOBALS['handlingAndComplete'][$tmp['hashId']] = TRUE;
            return $tmp;
        }
        else
        {
            return array();
        }*/
    }

    public static function addUser($userHashId, $param)
    {
        if(!$userHashId || !$param['url'])
        {
            //记录日志

            echo "LOST PARAM\n";
            print_r($param);
            return FALSE;
        }
        if(!isset($GLOBALS['waiting'][$userHashId]) && !isset($GLOBALS['handlingAndComplete'][$userHashId]))
        {
            $GLOBALS['waiting'][$userHashId] = $param;
        }
        echo "Add ".count($GLOBALS['waiting'])."\n";
    }

    public static function initPool()
    {
        $maxThreadNum = getConfig('maxThreadNum');

        for($i = 0; $i < $maxThreadNum; $i ++)
        {
            self::$pool[$i] = new handleUser();
            self::$pool[$i]->start();
            //每隔100毫秒初始化一个线程
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
            echo "Start Loop\n";
            //若为空则尝试自动获取
            if(count($this->userPageParam) <= 0 || !isset($this->userPageParam['url']))
            {
                echo "Start Loop2\n";
                $this->userPageParam = dispatching::getNextUser();
            }
            if(count($this->userPageParam) > 0)
            {
                echo "Start Loop3\n";
                print_r($this->userPageParam);
                $this->userPageParam = array();
                /*echo microtime()." Handle user {$this->userPageParam['hashId']}\n";
                print_r($this->userPageParam);
                echo "\n";
                $this->userPageParam = array();*/

                /*loadClass('userPage',$this->userPageParam);
                $this->userPageParam = array();*/
            }
            sleep(1);
        }
    }
}