<?php
/**
 * common.php
 * Author: Ethan
 * CreateTime: 2015/12/20 19:44
 * Description:
 */

/*
 * 自动加载需要的class并局部保存
 *
 * @param   string  class名称
 * @param   array  class初始化时的参数，以数组形式传递
 * @return  object
 */
if(!function_exists('loadClass'))
{
    function loadClass($class, $param = NULL)
    {
        if(!is_string($class))
        {
            //日志记录

            exit(-3);
        }
        static $commonClasses = array();
        if(isset($commonClasses[$class]) && $commonClasses[$class] instanceof $class)
        {
            return $commonClasses[$class];
        }

        $filePath = '';
        foreach(array(INCLUDE_DIR,SRC_DIR) as $path)
        {
            if(file_exists($path.'/'.$class.'.php'))
            {
                $filePath = $path;
                break;
            }
        }

        if(!empty($filePath) && !isLoadFile($class) && class_exists($class,false) === FALSE)
        {
             require_once $filePath.'/'.$class.'.php';
        }

        if(INCLUDE_DIR === $filePath)
        {
            $commonClasses[$class] = isset($param) ? new $class($param) : new $class();
            return $commonClasses[$class];
        }
        else if(SRC_DIR == $filePath)
        {
            return isset($param) ? new $class($param) : new $class();
        }
        return FALSE;
    }
}

/*
 * 判断是否已加载该类所在文件
 *
 * @param string class 类名称
 * @return boolean
 */
if(!function_exists('isLoadFile'))
{
    function isLoadFile($class = '')
    {
        static $commonIsLoaded = array();
        if(empty($class))
        {
            return FALSE;
        }
        if(isset($commonIsLoaded[$class]))
        {
            return TRUE;
        }
        else
        {
            $commonIsLoaded[$class] = 1;
            return FALSE;
        }
    }
}

/*
 * 从数组构建键值对
 *
 * @param   array  需要被重组的参数
 * @return  string
 */
if(!function_exists('buildParamFromArray'))
{
    function buildParamFromArray($arrParam)
    {
        if(!is_array($arrParam))
        {
            return FALSE;
        }
        $strResult = '';
        foreach($arrParam as $key=>$val)
        {
            if(is_array($val))
            {
                $val = buildParamFromArray($val);
            }
            $strResult .= $key.'='.$val.'&';
        }

        $strResult = substr($strResult,0,strlen($strResult)-1);
        return $strResult;
    }
}

/*
 * 写入数据至文件(未增加权限检验等措施)
 *
 * @param   string  文件名称
 * @param   string  文件内容
 * @param   string  文件写入方式
 * @return  object
 */
if(!function_exists('writeToFile'))
{
    function writeToFile($file, $content, $model = 'w')
    {
        if(!file_exists($file) && $model == 'r+')
        {
            return FALSE;
        }
        $handle = fopen($file,$model);
        flock($handle, LOCK_EX);
        fwrite($handle, $content);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/*
 * 获取配置信息
 *
 * @param   string  配置索引
 * @return  string
 */
if(!function_exists('getConfig'))
{
    function getConfig($key)
    {
        $configClass = loadClass('config');
        return $configClass->getConfig($key);
    }
}

/*
 * 从数组生成更新SQL更新语句
 *
 * @param   array   更新所有字段
 * @return string
 */
if(!function_exists('buildUpdateSql'))
{
    function buildUpdateSql($arrItem)
    {
        if(!is_array($arrItem))
        {
            return FALSE;
        }

        foreach($arrItem as $key => $val)
        {
            $arrItem[$key] = "{$key}='".addslashes(trim($val))."'";
        }

        return ' '.implode(',',$arrItem).' ';
    }
}