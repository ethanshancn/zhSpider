<?php
/**
 * index.php
 * Author: Ethan
 * CreateTime: 2015/12/16 21:55
 * Description: 入口文件
 */

defined('WEB_ROOT')    || define('WEB_ROOT',dirname(__FILE__));
defined('INCLUDE_DIR') || define('INCLUDE_DIR',WEB_ROOT.'/include');
defined('SRC_DIR')     || define('SRC_DIR',WEB_ROOT.'/src');
defined('RES_DIR')     || define('RES_DIR',WEB_ROOT.'/res');

defined('SL_DEBUG')   || define('SL_DEBUG',1);
defined('SL_ERROR')   || define('SL_ERROR',2);

//配置文件目录
defined('CONFIG_FILE') || define('CONFIG_FILE',RES_DIR.'/config.json');
//基础函数文件目录
defined('COMMON_FILE') || define('COMMON_FILE',WEB_ROOT.'/common.php');
//调度中心
defined('DISPATCH_FILE') || define('DISPATCH_FILE',WEB_ROOT.'/dispatching.php');

require_once COMMON_FILE;
require_once DISPATCH_FILE;

//登录并初始化XSRF常量
$loginClass = loadClass('login');
$xsrf = $loginClass->doLogin();

//线程资源池初始化
dispatching::initPool();

defined('XSRF') || define('XSRF',$xsrf);

loadClass('userPage',array('url' => 'https://www.zhihu.com/people/ethan_zhtest/followees'));