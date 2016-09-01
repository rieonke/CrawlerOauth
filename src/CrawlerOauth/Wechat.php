<?php
/**
 * Created by PhpStorm.
 * User: Rieon
 * Date: 2016/8/29
 * Time: 19:16
 */

namespace Rieon\CrawlerOauth;

use GuzzleHttp\Client;

class Wechat extends BaseAuth
{
    private $uuid;
    private $state;
    /**
     * url for wechat checking login state
     */
    const URL_CHECK = 'https://long.open.weixin.qq.com/connect/l/qrconnect?';
    /**
     * wechat open server the first time client redirect to
     */
    const URL_WECHAT_IMG_SERVER = 'https://open.weixin.qq.com';
    /**
     * regular for getting wxcode
     */
    const REG_CODE = "/(?<=wx_code=').*?(?=';)/";
    /**
     * regular for getting qrcode image path
     */
    const REG_QRCODE_IMG_PATH = "/(?<=lightBorder\" src=\").*?(?=\")/";
    /**
     * regular for gettiing uuid
     */
    const REG_UUID = "/(?<=uuid=).*?(?=\")/";
    /**
     * regular for getting state code
     */
    const REG_STATE = "/(?<=state=).*?(?=#wechat)/";


    /**
     * Wechat constructor.
     * @param $requestUrl
     * @param $callbackUrl
     * @param $qrcodeImagePath
     */
    public function __construct($requestUrl, $qrcodeImagePath)
    {
        parent::__construct($requestUrl, $qrcodeImagePath);
        $this->uuid = $this->getUuid();
        $this->state = $this->getState();
    }

    /**
     * @return bool|string
     */
    protected function get()
    {
        if ($this->getQrcodeImg()) {
            $code = $this->getCode();
            $cookies = $this->getCookies($code, $this->state);
            return $cookies;
        }
        return false;
    }

    /**
     * @return bool|string
     */
    private function getCode()
    {
        if ($this->uuid) {
            $code = '';
            $client = new Client();
            $config = [
                'verify' => false,
                'allow_redirects' => false,
                'query' => [
                    'uuid' => $this->uuid,
                    '_' => time()
                ]
            ];
            while (preg_match(self::REG_CODE, $client->get(self::URL_CHECK, $config)->getBody(), $code)) {
                if ($code[0] != '') {
                    break;
                }
            }
            return $code[0];
        }
        return false;
    }

    /**
     * @return bool|string
     */
    private function getQrcodeImg()
    {
        if (preg_match(self::REG_QRCODE_IMG_PATH, $this->targetPageContent, $image)) {
            $client = new Client();
            $res = $client->get(
                self::URL_WECHAT_IMG_SERVER . $image[0],
                ['verify' => false, 'allow_redirects' => false]
            );
            if ($res->getStatusCode() == '200') {
                return $this->storeImg($res->getBody());
            }
            return false;
        }
        return false;
    }

    /**
     * @return bool|string
     */
    private function getUuid()
    {
        if (preg_match(self::REG_UUID, $this->targetPageContent, $uuid)) {
            return $uuid[0];
        }
        return false;
    }

    /**
     * @return bool|string
     */
    private function getState()
    {
        if (preg_match(self::REG_STATE, $this->targetUrl, $state)) {
            return $state[0];
        }
        return false;
    }

    /**
     * @param $code
     * @param $state
     * @return string
     */
    private function getCookies($code, $state)
    {
        $client = new Client();
        return $client->get($this->callbackUrl,
            [
                'verify' => false,
                'allow_redirects' => false,
                'query' => [
                    'code' => $code,
                    'state' => $state
                ]
            ])->getHeaderLine("Set-Cookie");
    }
}