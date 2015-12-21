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

//配置文件目录
defined('CONFIG_FILE') || define('CONFIG_FILE',RES_DIR.'/config.json');
//基础函数文件目录
defined('COMMON_FILE') || define('COMMON_FILE',WEB_ROOT.'/common.php');

require_once COMMON_FILE;

//登录并初始化XSRF常量
$loginClass = loadClass('login');
$xsrf = $loginClass->doLogin();

defined('XSRF') || define('XSRF',$xsrf);

$index = loadClass('userPage',array('url' => 'https://www.zhihu.com/people/ethan_shan/followees'));
$index->startGet();