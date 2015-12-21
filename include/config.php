<?php
/**
 * config.php
 * Author: Ethan
 * CreateTime: 2015/12/20 19:40
 * Description: 配置文件处理
 */
class config
{
    public $arrConfig;

    public function __construct()
    {
        if(file_exists(CONFIG_FILE))
        {
            $this->arrConfig = json_decode(file_get_contents(CONFIG_FILE),TRUE);
        }
        else
        {
            //记录日志

            exit(-3);
        }
    }

    public function getConfig($key)
    {
        if(isset($this->arrConfig[$key]))
        {
            return $this->arrConfig[$key];
        }
        else
        {
            return FALSE;
        }
    }

    public function setConfig($key,$val)
    {
        if(!is_string($key) || !is_string($val))
        {
            //记录日志

            return FALSE;
        }
        $this->arrConfig[$key] = $val;
        $content = json_encode($this->arrConfig);
        writeToFile(CONFIG_FILE,$content);
        return TRUE;
    }


}