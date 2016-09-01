<?php
/**
 * Created by PhpStorm.
 * User: Rieon
 * Date: 2016/8/29
 * Time: 19:18
 */

namespace Rieon\CrawlerOauth;

use Rieon\Common\Curl\Curl;

class BaseAuth
{
    /*
     * request url for oauth login before redirect
     */
    protected $requestUrl;
    /*
     * callback url for get auth grant after signed
     */
    protected $callbackUrl;
    /*
     * path to save the qrcode image
     */
    protected $qrcodeImagePath;
    /*
     * the url redirect to the oauth server
     */
    protected $targetUrl;
    /*
     * the response of the oauth when first redirected
     */
    protected $targetPageContent;

    protected $originCookies;

    /**
     * the file name for the qrcode
     */
    const IMG_NAME = "/qrcode.jpg";


    /**
     * BaseAuth constructor.
     * @param $requestUrl
     * @param $qrcodeImagePath
     */
    protected function __construct($requestUrl,$qrcodeImagePath)
    {
        $this->requestUrl = $requestUrl;
        $this->qrcodeImagePath = $qrcodeImagePath;
        $this->targetUrl = $this->getTargetUrl();
        $this->targetPageContent = $this->getTargetPageContent();
        $this->callbackUrl = $this->getCallbackUrl($this->targetUrl);
    }

    /**
     * @return string
     */
    private function getTargetUrl(){
        $client = new Curl();
        $client->get($this->requestUrl);
        $url = $client->responseHeaders['Location'];
        $client->close();
        return $url;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    private function getTargetPageContent(){
        $client = new Curl();
        $res = $client->get($this->targetUrl);
        $client->close();
        return $res;
    }

    /**
     * @param $url
     * @return mixed
     */
    private function getCallbackUrl($url){
        $params = parse_url($url);
        parse_str($params['query'],$query);
        return $query['redirect_uri'];
    }

    /**
     * @return mixed
     */
    public function cookies()
    {
        return $this->get();
    }


    public function qrcode($option = false)
    {
        return $this->getQrcodeImg($option);
    }

    /**
     * @param $qrcode
     * @return string
     */
    protected function storeImg($qrcode)
    {
        $path = $this->qrcodeImagePath . self::IMG_NAME;
        file_put_contents($path, $qrcode);
        return $path;
    }

    protected function getQrcodeImg($option)
    {
        return false;
    }
}