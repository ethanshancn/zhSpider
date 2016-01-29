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

    private $redisConnect;

    public function __construct()
    {
        $this->dbConnect = loadClass('DB');
        $this->redisConnect = $this->dbConnect->redisConnect;
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

            $strSql = "UPDATE tbUserInfo SET ".buildUpdateSql($arrUserInf)." WHERE iLocalUserId={$iLocalUserId}";
        }
        else
        {
            $arrUserInf['dtAddTime'] = $arrUserInf['dtModifyTime'];
            $strSql = "INSERT INTO tbUserInfo SET ".buildUpdateSql($arrUserInf);
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
            logMsg(SL_ERROR,"参数错误!");
            echo "参数错误\n";
            exit(-3);
        }
        $strSql = "SELECT iLocalUserId FROM tbUserInfo WHERE sHashId='{$sHashId}' LIMIT 1";
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

    public function addQuestion($arrInf)
    {
        if(!$arrInf['iQuestionId'] || !$arrInf['sContent'] || !$arrInf['sQuestionURL'])
        {
            return -1;
        }

        $strSql = "SELECT iLocalQuestionId FROM tbQuestion WHERE iQuestionId={$arrInf['iQuestionId']}";
        $result = $this->dbConnect->query($strSql);

        if(count($result) > 0)
        {
            return FALSE;
        }

        $arrInf['dtAddTime'] = time();
        $strSql = "INSERT INTO tbQuestion SET ".buildUpdateSql($arrInf);
        return $this->dbConnect->exec($strSql);
    }

    public function addAnswer($arrAnswerInf)
    {
        if(!is_array($arrAnswerInf))
        {
            return -1;
        }
        $iLocalAnswerId = $this->checkAnswerExist($arrAnswerInf['iAnswerId'], $arrAnswerInf['sHashId']);

        if($iLocalAnswerId === FALSE)
        {
            $arrAnswerInf['dtAddTime'] = time();
            $strSql = "INSERT INTO tbAnswer SET ".buildUpdateSql($arrAnswerInf);
            return $this->dbConnect->exec($strSql);
        }
        return 0;
    }

    public function checkAnswerExist($iAnswerId,$sHashId)
    {
        if(!is_string($sHashId) || !$iAnswerId)
        {
            logMsg(SL_ERROR,"参数错误!");
            echo "参数错误\n";
            exit(-3);
        }
        $strSql = "SELECT iLocalAnswerId FROM tbAnswer WHERE iAnswerId={$iAnswerId} AND sHashId='{$sHashId}' LIMIT 1";
        $result = $this->dbConnect->query($strSql);
        if(is_array($result) && isset($result[0]) && isset($result[0]['iLocalAnswerId']))
        {
            return $result[0]['iLocalAnswerId'];
        }
        else
        {
            return FALSE;
        }
    }

    //增加等待获取信息的用户(等待集合:userWaitSet;等待hash表:userWaitMap;进行和完成集合：handlingAndCompleteSet)
    public function addWait($hashId,$arrUserParam)
    {
        if(!$this->redisConnect->sIsMember('userWaitSet',$hashId) && !$this->redisConnect->hExists('userWaitMap',$hashId) && !$this->redisConnect->sIsMember('handlingAndCompleteSet',$hashId))
        {
            $this->redisConnect->multi()
                ->sAdd('userWaitSet',$hashId)
                ->hSet('userWaitMap',$hashId,json_encode($arrUserParam))
                ->exec();
        }
    }

    //获取下一个用户信息
    public function getNext()
    {
        if($this->redisConnect->sCard('userWaitSet') > 0)
        {
            $hashId = $this->redisConnect->sPop('userWaitSet');
            $arrReturn = $this->redisConnect->multi()
                ->hGet('userWaitMap',$hashId)
                ->sAdd('handlingAndCompleteSet',$hashId)
                ->hDel('userWaitMap',$hashId)
                ->exec();
            return json_decode($arrReturn[0],TRUE);
        }
        return NULL;
    }

}