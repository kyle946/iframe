<?php

/**
 * index
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */

//应用名称
define("APP_NAME", 'app');
define('URL_REWRITE',  true); //URL重写  ， 主要是匹配 路由时会用到
//加载框架入口文件
include ( dirname(__FILE__) ."/iframe/i.php");
define('MAIN_DOMAIN', $_SERVER['HTTP_HOST']);
//运行项目 
iframe::start();
?>
