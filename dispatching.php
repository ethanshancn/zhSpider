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
            self::$pool[$i]->start(PTHREADS_INHERIT_ALL | PTHREADS_ALLOW_GLOBALS);
            self::$pool[$i]->join();
            //每隔50毫秒初始化一个线程
            usleep(50);
        }

    }
}

class handleUser extends Thread
{
    public function run()
    {
        $dbModel = loadClass('DBModel');
        $userPageParam = array();
        while(1)
        {
            //若为空则尝试自动获取
            if(count($userPageParam) <= 0 || !isset($userPageParam['url']))
            {
                $userPageParam = $dbModel->getNext();
            }
            if(is_array($userPageParam) && count($userPageParam) > 0)
            {
                logMsg(SL_DEBUG,"Start to handle user : ".json_encode($userPageParam));
                loadClass('userPage',$userPageParam);
                $userPageParam = array();
            }
            usleep(500);
        }
    }
}