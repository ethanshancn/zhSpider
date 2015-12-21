<?php
/**
 * answers.php
 * Author: Ethan
 * CreateTime: 2015/12/20 15:25
 * Description:
 */

class answers
{
    private $answerUrl;
    private $hashId;
    private $totalAnswer;
    private $curlObj;

    public function __construct($param)
    {
        if(!$param['answerUrl'] || !$param['hashId'] || !$param['totalAnswer'])
        {
            return FALSE;
        }
        $this->answerUrl = $param['answerUrl'];
        $this->hashId = $param['hashId'];
        $this->totalAnswer = $param['totalAnswer'];

        $this->curlObj = loadClass('zhCurl');

        $this->startGet();
    }

    public function startGet()
    {
        $page = 1;
        while(($page-1) * 20 < $this->totalAnswer)
        {
            $this->getList($page ++);
        }
    }

    public function getList($page)
    {
        $url = $this->answerUrl.'?page='.$page;
        $result = $this->curlObj->getWebPage($url,array(CURLOPT_HTTPGET => TRUE));

        $file = dirname(__FILE__).'/../res/answer/page_'.$page.'.html';
        $handle = fopen($file,'w+');
        fwrite($handle, $result['content']);
        fclose($handle);
    }


}