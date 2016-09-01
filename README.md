# CrawlerOauth

an php oauth lib for web crawler

## 用途

在爬取一个只有第三方Oauth登陆的网站时，通常需要扫码登陆，这是一个比较繁琐的过程，这个包为你解决了这个问题。

## 支持的Oauth服务提供商

- 微信 (new Rieon\CrawlerOauth\Weixin)
- 微博 (new Rieon\CrawlerOauth\Weibo)
- 更多正在路上了~~~

## 正确食用方法

- 安装这个包
```
composer require rieon/crawler-oauth
```
- 引入并实例化(以微博为例)
```
use Rieon\CrawlerOauth\Weibo;

$weibo = new Weibo('Oauth请求地址', '二维码保存目录');

```
- 得到Cookies
```
$weibo->cookies()
```
## 额外:

- 获得二维码路径
```
$weibo->qrcode();
```
- 获得二维码图像
```
$weibo->qrcode(true);
```
- 参数说明

> Oauth请求地址: Oauth客户端的地址，重定向到Oauth服务器之前的那个地址
>
> 二维码保存目录即为Oauth登陆时扫描二维码的保存目录，相对绝对

## 例子
在 `example`目录中有一个index.php
```
浏览器中访问或者bash中执行php inde.php 之后扫描目录下的二维码即可得到cookies
```

## 方法
- cookies() 获得cookies
- qrcode() 获得二维码路径
- qrcode(true) 获得二维码图像

## License
MIT

