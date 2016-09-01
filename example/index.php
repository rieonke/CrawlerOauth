<?php
require_once __DIR__."/../vendor/autoload.php";

use Rieon\CrawlerOauth\Weibo;

$weibo = new Weibo("http://www.anshi7.com/oauth/weibo/", '.');

var_dump($weibo->qrcode());

var_dump($weibo->cookies());