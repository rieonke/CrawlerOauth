<?php
/**
 * Created by PhpStorm.
 * User: Rieon
 * Date: 2016/8/29
 * Time: 19:17
 */

namespace Rieon\CrawlerOauth;


use Rieon\Common\Curl\Curl;

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
     * @return array|bool
     */
    protected function get()
    {
        if ($this->getQrcodeImg()) {
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
        $client = new Curl();
        $config = $this->getRequestParams();
        $res = $client->get(self::URL_WEIBO_OAUTH_SERVER,$config);
        $r = json_decode($res, true);
        $this->qrcodeUrl = $r['url'];
        $this->vcode = $r['vcode'];
        $client->close();
    }

    /**
     * @param bool $option
     * @return bool|\Psr\Http\Message\StreamInterface|string
     */
    protected function getQrcodeImg($option = false)
    {
        $client = new Curl();
        $res = $client->get($this->qrcodeUrl);
        $client->close();
        if ($res != '') {
            $path = $this->storeImg($res);
            if ($option) {
                return $res;
            }
            return $path;
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
        $client = new Curl();
        do {
            $res = $client->get(self::URL_CHECK, $params);
            $r = json_decode($res, true);
        } while ($r['status'] != 3);
        $client->close();
        return $r['url'];
    }

    /**
     * @param $url
     * @return array
     */
    private function getCookies($url)
    {
        $client = new Curl();
        $client->get($url);
        $cookies = $client->getResponseCookies();
        $client->close();
        return $cookies;
    }
}