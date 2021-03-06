<?php
/**
 * Created by PhpStorm.
 * User: Rieon
 * Date: 2016/8/29
 * Time: 19:16
 */

namespace Rieon\CrawlerOauth;

use Rieon\Common\Curl\Curl;

class Wechat extends BaseAuth
{
    private $uuid;
    private $state;
    /**
     * url for wechat checking login state
     */
    const URL_CHECK = 'https://long.open.weixin.qq.com/connect/l/qrconnect';
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
     * @param $qrcodeImagePath
     */
    public function __construct($requestUrl, $qrcodeImagePath)
    {
        parent::__construct($requestUrl, $qrcodeImagePath);
        $this->uuid = $this->getUuid();
        $this->state = $this->getState();
    }

    /**
     * @return array|bool
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
            $client = new Curl();
            $config = [
                    'uuid' => $this->uuid,
                    '_' => time()
            ];
            while (preg_match(self::REG_CODE, $client->get(self::URL_CHECK, $config), $code)) {
                if ($code[0] != '') {
                    break;
                }
            }
            $client->close();
            return $code[0];
        }
        return false;
    }

    /**
     * @param bool $option
     * @return bool|\Psr\Http\Message\StreamInterface|string
     */
    protected function getQrcodeImg($option = false)
    {
        if (preg_match(self::REG_QRCODE_IMG_PATH, $this->targetPageContent, $image)) {
            $client = new Curl();
            $res = $client->get(self::URL_WECHAT_IMG_SERVER . $image[0]);
            if ($res != null) {
                $path = $this->storeImg($res);
                if ($option) {
                    return $res;
                }
                return $path;
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
     * @return array
     */
    private function getCookies($code, $state)
    {
        $client = new Curl();
        $client->get($this->callbackUrl, ['code' => $code, 'state' => $state]);
        $cookies = $client->getResponseCookies();
        $client->close();
        return $cookies;
    }
}