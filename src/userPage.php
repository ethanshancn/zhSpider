<?php
/**
 * userPage.php
 * Author: Ethan
 * CreateTime: 2015/12/17 16:16
 * Description:解析用户关注者页面并整理获取个人信息（关注者页面已包含所需要的个人信息）
 */
class userPage
{
    private $webUrl;
    private $curlObj;
    private $hashId;
    private $dbObj;

    /*
     * $param中'url'必填，'hashId'选填
     */
    public function __construct($param)
    {
        $this->webUrl = $param['url'];
        $this->curlObj = loadClass('zhCurl');
        if(!empty($param['hashId']))
        {
            $this->hashId = $param['hashId'];
        }
        $this->dbObj = loadClass('DB');
    }

    public function startGet()
    {

        $result = $this->curlObj->getWebPage($this->webUrl,array(CURLOPT_HTTPGET => TRUE));
        if($result['errno'] != 0)
        {
            //打印日志

            return FALSE;
        }

        $this->getUserInfo($result['content']);

        //$this->getUserFollowee();
        //$this->getUserAnswer();

    }

    public function getUserInfo($content)
    {
        $webSite = loadClass('parserDom',$content);

        //初始化hashId
        if(empty($this->hashId))
        {
            $hashParam = $webSite->find("div.zh-general-list",0)->getAttr('data-init');
            $hashParam = htmlspecialchars_decode($hashParam);
            $hashParam = json_decode($hashParam,TRUE);
            $this->hashId = $hashParam['params']['hash_id'];
        }

        $userInfo = array();
        $infoDiv = $webSite->find("div.zm-profile-header",0);

        $userInfo['sUserName'] = ($tmp = $infoDiv->find("a.name",0))? $tmp->getPlainText() : '';
        $userInfo['sHashId'] = $this->hashId;
        $userInfo['sLocation'] = (($tmp = $infoDiv->find("span.location",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
        $userInfo['sBusiness'] = (($tmp = $infoDiv->find("span.business",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
        $userInfo['iSex'] = (strstr(((($tmp = $infoDiv->find("span.gender",0)) && ($tmp2 = $tmp->firstChild()))? $tmp2->getAttr("class") : 'male'),'female') === FALSE) ? 1 : 0;
        $userInfo['sEmployment'] = ($tmp = $infoDiv->find("span.employment",0)) ? $tmp->getPlainText() : '';
        $userInfo['sPosition'] = ($tmp = $infoDiv->find("span.position",0)) ? $tmp->getPlainText() : '';
        $userInfo['sSignature'] = ($tmp = $infoDiv->find("span.bio",0)) ? $tmp->getPlainText() : '';
        $userInfo['sDescription'] = ($tmp = $infoDiv->find("span.description",0) && $tmp2 = $tmp->firstChild()) ? $tmp2->getPlainText() : '';
        $userInfo['iAgree'] = (($tmp = $infoDiv->find("span.zm-profile-header-user-agree",0)) && ($tmp2 = $tmp->find("strong",0))) ? $tmp2->getPlainText() : '';
        $userInfo['iThanks'] = (($tmp = $infoDiv->find("span.zm-profile-header-user-thanks",0)) && ($tmp2 = $tmp->find("strong",0))) ? $tmp2->getPlainText() : '';

        unset($infoDiv,$webSite);

        print_r($userInfo);

    }

    public function getUserAnswer()
    {
        /********************** 测试信息 ******************************/
        $userPage = 'https://www.zhihu.com/people/qingwan/followees';
        $hashId = '8b7b027115602273520d871daa3dc475';
        $totalAnswer = 109;
        /********************** 测试信息 ******************************/

        $userPage = substr($userPage,0,strrpos($userPage,'/') + 1).'answers';
        $answer = loadClass('answer',array('answerUrl'=>$userPage,'hashId'=>$hashId,'totalAnswer'=>$totalAnswer));

    }

    public function getUserFollowee()
    {
        $followees = new followees();
        $followees->startGet($this->hashId);
    }

}