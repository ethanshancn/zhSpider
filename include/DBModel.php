<?php
/**
 * DBModel.php
 * Author: Ethan
 * CreateTime: 2016/1/4 11:18
 * Description:相关数据库应用功能封装(数据库返回错误时都以-1返回)
 */
class DBModel
{
    private $dbConnect;

    public function __construct()
    {
        $this->dbConnect = loadClass('DB');
    }

    public function addUserInf($arrUserInf)
    {
        if(!is_array($arrUserInf))
        {
            return -1;
        }
        $iLocalUserId = $this->checkUserIsExist($arrUserInf['sHashId']);
        $arrUserInf['dtModifyTime'] = time();
        if($iLocalUserId !== FALSE && $iLocalUserId > 0)
        {

            $strSql = "UPDATE tbuserinfo SET ".buildUpdateSql($arrUserInf)." WHERE iLocalUserId={$iLocalUserId}";
        }
        else
        {
            $arrUserInf['dtAddTime'] = $arrUserInf['dtModifyTime'];
            $strSql = "INSERT INTO tbuserinfo SET ".buildUpdateSql($arrUserInf);
        }
        return $this->dbConnect->exec($strSql);
    }

    /*
     * 检查用户是否存在
     * 存在返回本地ID，不存在返回FALSE
     */
    public function checkUserIsExist($sHashId)
    {
        if(!is_string($sHashId))
        {
            //记录日志

            echo "参数错误\n";
            exit(-3);
        }
        $strSql = "SELECT iLocalUserId FROM tbuserinfo WHERE sHashId='{$sHashId}' LIMIT 1";
        $result = $this->dbConnect->query($strSql);

        if(is_array($result) && isset($result[0]) && isset($result[0]['iLocalUserId']))
        {
            return $result[0]['iLocalUserId'];
        }
        else
        {
            return FALSE;
        }
    }
}