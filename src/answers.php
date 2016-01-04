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
    private $dbModel;

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
        $this->dbModel = loadClass('DBModel');

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

        $result['content'];
        $webSite = loadClass('parserDom',$result['content']);
        $webSite->find("#zh-profile-answer-list");

        $webSite = loadClass('parserDom',$result['content']);
        $answerList = $webSite->find("#zh-profile-answer-list",0)->getChildList();
        unset($webSite);

        foreach($answerList as $key=>$val)
        {
            $arrInf = array();
            $tmp = $val->firstChild()->firstChild();
            $arrTmp = $this->dealAnswerHref($tmp->getAttr("href"));
            $arrInf['iQuestionId'] = $arrTmp['iQuestionId'];
            $arrInf['sContent'] = $tmp->getPlainText();
            $arrInf['sQuestionURL'] = $arrTmp['sQuestionURL'];
            $this->dbModel->addQuestion($arrInf);
            $arrInf = array(
                'iQuestionId' => $arrTmp['iQuestionId'],
                'iAnswerId' => $arrTmp['iAnswerId'],
                'sAnswerURL'=>$arrTmp['sAnswerURL']
            );
            unset($tmp,$arrTmp);
            $arrInf['sHashId'] = $this->hashId;
            $arrInf['sContent'] = (($tmp = $val->find("div.zm-item-rich-text",0)) && ($tmp2 = $tmp->firstChild())) ? $tmp2->getPlainText() : '';
            $arrInf['iVoteUp'] = (($tmp = $val->find("button.up",0)) && ($tmp2 = $tmp->getChild(1))) ? $tmp2->getPlainText() : 0;
            $this->dbModel->addAnswer($arrInf);
        }

    }

    private function dealAnswerHref($answerUrl)
    {
        $arrInf = explode('/',trim($answerUrl,'/'));
        $arrReturn = array(
            'iQuestionId' => $arrInf[1],
            'iAnswerId'   => $arrInf[3],
            'sQuestionURL'=> 'https://www.zhihu.com/question/'.$arrInf[1],
            'sAnswerURL'  => 'https://www.zhihu.com/question/'.$arrInf[1].'/answer/'.$arrInf[3]
        );
        return $arrReturn;
    }
}