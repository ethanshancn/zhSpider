<?php
/**
 * login.php
 * Author: Ethan
 * CreateTime: 2015/12/18 9:35
 * Description:登录模块
 */

class login
{
    public function doLogin()
    {
        $curlObj = loadClass('zhCurl');
        //获取登陆页
        $loginSite = "https://www.zhihu.com/";
        $loginHtml = $curlObj->getWebPage($loginSite);
        $html = loadClass('simple_html_dom');
        $html->load($loginHtml['content']);
        $xsrf = $html->find('input[name=_xsrf]',0)->value;
        $html->clear();

        //获取验证码并从CLI输入
        $captcha = "https://www.zhihu.com/captcha.gif?r=".time().rand(200,999);
        $result = $curlObj->getWebPage($captcha);
        $captchaFile = dirname(__FILE__).'/../res/login.gif';
        $handle = fopen($captchaFile,'w+');
        fwrite($handle,$result['content']);
        fclose($handle);

        fwrite(STDOUT,"Pleate check the login.gif in project 'res' foler and enter it:\n");
        $captchaContent = trim(fgets(STDIN));

        $postParam = array(
            '_xsrf' => $xsrf,
            'email' => getConfig('zhAccount'),
            'password'=>getConfig('zhPassword'),
            'remember_me'=>'true',
            'captcha'=>$captchaContent
        );
        $postUrl = 'https://www.zhihu.com/login/email';
        $result = $curlObj->getWebPage($postUrl,array(CURLOPT_POSTFIELDS => buildParamFromArray($postParam)));
        $loginResult = json_decode($result['content'],TRUE);
        if($loginResult['r'] == 0)
        {
            fwrite(STDOUT,"Login Success\n");
        }
        else
        {
            fwrite(STDOUT,"Login Failed: {$loginResult['msg']}\n");
        }
        return $xsrf;
    }
}