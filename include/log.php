<?php
/**
 * log.php
 * Author: Ethan
 * CreateTime: 2015/12/18 9:48
 * Description:日志记录文件
 */
class log
{
    private $dbConnect;
    private $dbName;

    public function __construct()
    {
        $this->dbConnect = loadClass('DB');
        $this->initDB();
    }

    private function initDB()
    {
        $filedName = 'Tables_in_'.getConfig('mysqlDBName');
        $arrTable = $this->dbConnect->query("SHOW TABLES WHERE {$filedName} LIKE 'tbLog_%'");
        $today = date("Ymd");
        $buildToday = FALSE;
        $overTime = intval(date("Ymd",(strtotime($today)-86400*intval(getConfig('maxLogDays')))));
        foreach($arrTable as $key=>$val)
        {
            $tableDate = intval(substr($val[$filedName],6));
            if($tableDate <= $overTime)
            {
                $this->dbConnect->exec("DROP TABLE tblog_".$tableDate);
            }
            else if($tableDate == $today)
            {
                $buildToday = TRUE;
            }
        }

        if($buildToday === FALSE)
        {
            $sqlStr = 'CREATE TABLE `tbLog_'.$today.'` (`sTime` datetime NOT NULL,`iLevel` int(11) DEFAULT \'1\' COMMENT \'错误等级（1:正常debug记录;2:错误输出记录）\',`sInfo` text,`sPath` varchar(255) DEFAULT NULL) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;';
            $this->dbConnect->exec($sqlStr);
        }

        $this->dbName = "tbLog_".$today;
    }

    public function addLog($iLevel, $sInfo, $sPath)
    {
        if(!$sInfo || !$sPath)
        {
            logMsg(SL_ERROR,'Log param Error, now the sInfo is '.$sInfo.' sPath is '.$sPath);
            return FALSE;
        }

        $strSql = "INSERT INTO {$this->dbName} (`sTime`,`iLevel`,`sInfo`,`sPath`) VALUES (NOW(),'{$iLevel}','{$sInfo}','{$sPath}')";
        return $this->dbConnect->exec($strSql);
    }
}