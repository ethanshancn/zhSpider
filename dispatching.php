<?php
/**
 * dispatching.php
 * Author: Ethan
 * CreateTime: 2016/1/6 20:53
 * Description: 线程调度器
 */

class dispatching
{
    private static $pool = array();

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
        $dbModel = loadClass('DBModel');
        while(1)
        {
            echo "Start Loop\n";
            //若为空则尝试自动获取
            if(count($this->userPageParam) <= 0 || !isset($this->userPageParam['url']))
            {
                echo "Start Loop2\n";
                $this->userPageParam = $dbModel->getNext();
            }
            if(is_array($this->userPageParam) && count($this->userPageParam) > 0)
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