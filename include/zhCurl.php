<?php
/**
 * zhCurl.php
 * Author: Ethan
 * CreateTime: 2015/12/17 12:32
 * Description:
 */
class zhCurl
{
    public $curlHandle;

    public function __construct()
    {
        $this->curlHandle = curl_init();
    }

    private function initOption ($otherOpt)
    {
        $cookieDir = dirname(__FILE__).'\..\res\zhihu.cookie';
        $defaultOpt = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36",
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => true,     // Enabled SSL Cert checks
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO         => dirname(__FILE__).'/../res/GeoTrustGlobalCA.crt',
            CURLOPT_COOKIEFILE     => $cookieDir,
            CURLOPT_COOKIEJAR      => $cookieDir
        );
        if(is_array($otherOpt))
        {
            return $otherOpt + $defaultOpt;
        }
        else
        {
            return $defaultOpt;
        }
    }

    public function getWebPage($webUrl,$otherOpt = NULL)
    {
        if(!is_array($otherOpt))
        {
            $otherOpt = array();
        }
        $arrOpt = $this->initOption(array(CURLOPT_URL => $webUrl) + $otherOpt);

        print_r($arrOpt);

        curl_setopt_array($this->curlHandle,$arrOpt);
        $content = curl_exec( $this->curlHandle );
        $err     = curl_errno( $this->curlHandle );
        $errmsg  = curl_error( $this->curlHandle );
        $header  = curl_getinfo( $this->curlHandle );

        $arrReturn['errno']   = $err;
        $arrReturn['errmsg']  = $errmsg;
        $arrReturn['header']  = $header;
        $arrReturn['content'] = $content;
        return $arrReturn;
    }

    public function __destruct()
    {
        curl_close($this->curlHandle);
    }
}