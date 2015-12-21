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

    public function __construct($param)
    {
        $this->webUrl = ($param['url'] == NULL) ? 'https://www.zhihu.com/node/ProfileFolloweesListV2' : $param['url'];
        $this->curlObj = loadClass('zhCurl');
    }

    public function startGet($hashId)
    {
        if(empty($hashId) || !is_string($hashId))
        {
            return FALSE;
        }

        $defaultParam = array(
            'method' => 'next',
            'params' => array(
                'offset'  => -20,
                'order_by'=> 'created',
                'hash_id' => $hashId
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
        $result = json_decode($result['content'],TRUE);

        if(count($result) > 0)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



}