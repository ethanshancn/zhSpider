<?php
/**
 * followees.php
 * Author: Ethan
 * CreateTime: 2015/12/18 21:57
 * Description:关注者
 */

class followees
{
    private $webUrl;
    private $curlObj;
    private $hashId;

    public function __construct($param)
    {
        if(!$param['hashId'])
        {
            return FALSE;
        }
        $this->webUrl = (isset($param['url']) && !empty($param['url'])) ? $param['url'] : 'https://www.zhihu.com/node/ProfileFolloweesListV2';
        $this->hashId = $param['hashId'];
        $this->curlObj = loadClass('zhCurl');

        $this->startGet();
    }

    public function startGet()
    {
        $defaultParam = array(
            'method' => 'next',
            'params' => array(
                'offset'  => -20,
                'order_by'=> 'created',
                'hash_id' => $this->hashId
            ),
            '_xsrf'  => XSRF
        );

        do
        {
            $defaultParam['params']['offset'] += 20;
            $postParam = $defaultParam;
            $postParam['params'] = urlencode(json_encode($postParam['params']));
            $result = $this->getList(buildParamFromArray($postParam));
        }
        while($result);
    }

    public function getList($param)
    {
        $result = $this->curlObj->getWebPage($this->webUrl,array(CURLOPT_POSTFIELDS => $param));
        $content = json_decode($result['content'],TRUE);
        $webSite = loadClass('parserDom',$content);
        $userList = $webSite->find("div.zm-profile-card");
        unset($webSite);
        foreach($userList as $val)
        {
            $arrInf['hashId'] = $val->find("button.zm-rich-follow-btn",0)->getAttr("data-id");
            $arrInf['url'] = $val->find("a.zg-link",0)->getAttr("href").'/followees';
            loadClass('userPage',$arrInf);
        }
    }
}