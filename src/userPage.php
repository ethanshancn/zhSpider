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

        //$this->getUserFollowee();
        //$this->getUserAnswer();

    }

    public function getUserInfo($content)
    {
        $webSite = loadClass('simple_html_dom');
        $webSite->load($content);

        //初始化hashId
        if(empty($this->hashId))
        {
            $hashParam = $webSite->find("div[class=zh-general-list]",0)->getAttribute('data-init');
            $hashParam = htmlspecialchars_decode($hashParam);
            $hashParam = json_decode($hashParam,TRUE);
            $this->hashId = $hashParam['params']['hash_id'];
        }





        $webSite->clear();
    }

    public function getUserAnswer()
    {
        /********************** 测试信息 ******************************/
        $userPage = 'https://www.zhihu.com/people/qingwan/followees';
        $hashId = '8b7b027115602273520d871daa3dc475';
        $totalAnswer = 109;
        /********************** 测试信息 ******************************/

        $userPage = substr($userPage,0,strrpos($userPage,'/') + 1).'answers';
        $answer = new answers($userPage,$hashId,$totalAnswer);

    }

    public function getUserFollowee()
    {
        $followees = new followees();
        $followees->startGet($this->hashId);
    }

}