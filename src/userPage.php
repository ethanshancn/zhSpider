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
    private $dbModel;

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
        $this->dbModel = loadClass('DBModel');

        $this->startGet();
    }

    public function startGet()
    {

        $result = $this->curlObj->getWebPage($this->webUrl,array(CURLOPT_HTTPGET => TRUE));
        if($result['errno'] != 0)
        {
            //打印日志

            return FALSE;
        }

        if($userInf = $this->getUserInfo($result['content']))
        {
            //若未成功插入用户数据(包含插入失败以及用户已存在两种情况),则不进行如下操作
            $this->getUserAnswer($userInf['totalAnswer']);
            $this->getUserFollowee();
        }

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

        //用户不存在时进行插入
        if($this->dbModel->checkUserIsExist($this->hashId) === FALSE)
        {
            $userInfo = $arrReturn = array();
            $infoDiv = $webSite->find("div.zm-profile-header",0);
            unset($webSite);

            $userInfo['sUserName'] = ($tmp = $infoDiv->find("a.name",0))? $tmp->getPlainText() : '';
            $userInfo['sHashId'] = $this->hashId;
            $userInfo['sLocation'] = (($tmp = $infoDiv->find("span.location",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
            $userInfo['sBusiness'] = (($tmp = $infoDiv->find("span.business",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
            $userInfo['iSex'] = (strstr(((($tmp = $infoDiv->find("span.gender",0)) && ($tmp2 = $tmp->firstChild()))? $tmp2->getAttr("class") : 'male'),'female') === FALSE) ? 1 : 0;
            $userInfo['sEmployment'] = ($tmp = $infoDiv->find("span.employment",0)) ? $tmp->getPlainText() : '';
            $userInfo['sPosition'] = ($tmp = $infoDiv->find("span.position",0)) ? $tmp->getPlainText() : '';
            $userInfo['sSignature'] = ($tmp = $infoDiv->find("span.bio",0)) ? $tmp->getPlainText() : '';
            $userInfo['sDescription'] = (($tmp = $infoDiv->find("span.description",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
            $userInfo['iAgree'] = (($tmp = $infoDiv->find("span.zm-profile-header-user-agree",0)) && ($tmp2 = $tmp->find("strong",0))) ? $tmp2->getPlainText() : '';
            $userInfo['iThanks'] = (($tmp = $infoDiv->find("span.zm-profile-header-user-thanks",0)) && ($tmp2 = $tmp->find("strong",0))) ? $tmp2->getPlainText() : '';
            $userInfo['sAvater'] = ($tmp = $infoDiv->find("img.avatar",0)) ? $tmp->getAttr("src") : '';
            $userInfo['sUniqueName'] = str_replace('/followees','',str_replace('https://www.zhihu.com/people/','',$this->webUrl));

            $arrReturn = $userInfo;
            $arrReturn['totalAnswer'] = (($tmp = $infoDiv->find("div.profile-navbar",0)->getChild(2)) && ($tmp2 = $tmp->find("span",0))) ? $tmp2->getPlainText() : 0;

            unset($infoDiv);

            $this->dbModel->addUserInf($userInfo);
            return $arrReturn;
        }
        else
        {
            return FALSE;
        }
    }

    public function getUserAnswer($totalNum = 0)
    {
        $userPage = substr($this->webUrl,0,strrpos($this->webUrl,'/') + 1).'answers';
        loadClass('answers',array('answerUrl'=>$userPage,'hashId'=>$this->hashId,'totalAnswer'=>$totalNum));
    }

    public function getUserFollowee()
    {
        loadClass('followees',array('hashId'=>$this->hashId));
    }

}