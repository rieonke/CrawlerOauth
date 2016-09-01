<?php
/**
 * Created by PhpStorm.
 * User: Rieon
 * Date: 2016/8/29
 * Time: 19:17
 */

namespace Rieon\CrawlerOauth;


use GuzzleHttp\Client;

class Weibo extends BaseAuth
{
    /*
     * weibo clientId
     */
    private $clientId;
    /*
     * qrcode url
     */
    private $qrcodeUrl;
    /*
     * vcode
     */
    private $vcode;
    /*
     * weibo server for generate qrcode and etc...
     */
    const URL_WEIBO_OAUTH_SERVER = "https://api.weibo.com/oauth2/qrcode_authorize/generate";
    /*
     * check the auth state
     */
    const URL_CHECK = "https://api.weibo.com/oauth2/qrcode_authorize/query";

    /**
     * Weibo constructor.
     * @param $requestUrl
     * @param $qrcodeImagePath
     */
    public function __construct($requestUrl, $qrcodeImagePath)
    {
        parent::__construct($requestUrl, $qrcodeImagePath);
        $this->clientId = $this->getClientId();
        $this->init();
    }

    /**
     * @return bool|string
     */
    protected function get()
    {
        if($this->getQrcodeImg()){
            $url = $this->getSuccessUrl();
            return $this->getCookies($url);
        }
        return false;
    }

    /**
     * @return mixed
     */
    private function getClientId()
    {
        $params = parse_url($this->targetUrl);
        parse_str($params['query'], $query);
        return $query['client_id'];
    }

    /**
     * @return array
     */
    private function getRequestParams()
    {
        return [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->callbackUrl,
            'response_type' => 'code',
            'state' => '',
            'scope' => '',
            '__rnd' => time() . "000"
        ];
    }

    /**
     * get qrcode url and vcode
     */
    private function init()
    {
        $client = new Client();
        $config = [
            'verify' => false,
            'allow_redirects' => false,
            'query' => $this->getRequestParams()
        ];
        $res = $client->get(self::URL_WEIBO_OAUTH_SERVER, $config);
        $r = json_decode($res->getBody(), true);
        $this->qrcodeUrl = $r['url'];
        $this->vcode = $r['vcode'];
    }

    /**
     * @return bool|string
     */
    private function getQrcodeImg()
    {
        $client = new Client();
        $res = $client->get($this->qrcodeUrl,['verify' => false, 'allow_redirects' => false]);
        if ($res->getStatusCode() == 200 && $res->getBody() != '') {
            return $this->storeImg($res->getBody());
        }
        return false;
    }

    /**
     * @return mixed
     */
    private function getSuccessUrl()
    {
        $params = [
            'vcode' => $this->vcode,
            '__rnd' => time() . "000"
        ];
        $config = [
            'verify' => false,
            'allow_redirects' => false,
            'query' => $params
        ];
        $client = new Client();
        do {
            $res = $client->get(self::URL_CHECK, $config)->getBody();
            $r = json_decode($res, true);
        } while ($r['status'] != 3);
        return $r['url'];
    }

    /**
     * @param $url
     * @return string
     */
    private function getCookies($url)
    {
        $client = new Client();
        return $client->get($url,
            [
                'verify' => false,
                'allow_redirects' => false
            ])->getHeaderLine("Set-Cookie");
    }
}