<?php
/**
 * DB.php
 * Author: Ethan
 * CreateTime: 2015/12/17 11:43
 * Description: 数据库相关封装(目前较弱，临时封装)
 */
class DB
{
    public $dbConnect;

    public function __construct()
    {
        $this->connectDB();
    }

    private function connectDB()
    {
        try
        {
            $this->dbConnect = new PDO('mysql:host='.getConfig('mysqlHost').';dbname='.getConfig('mysqlDBName'), getConfig('mysqlAccount'), getConfig('mysqlPassword'));
            $this->dbConnect->exec("set names utf8");
        }
        catch (PDOException $e)
        {
            //记录日志

            echo $e->getMessage()."\n";
            exit(-3);
        }
    }

    public function query($strSql)
    {
        try
        {
            $arrResult = $this->dbConnect->query($strSql);
        }
        catch (PDOException $e)
        {
            //记录日志

            echo $e->getMessage()."\n";
            exit(-3);
        }

        return $arrResult->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exec($strSql)
    {
        try
        {
            $iResult = $this->dbConnect->exec($strSql);
        }
        catch (PDOException $e)
        {
            //记录日志

            echo $e->getMessage()."\n";
            exit(-3);
        }
        return $iResult;
    }
}