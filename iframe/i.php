<?php

/** 
 * 引入文件
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */


//php版本必须在5.3.0或以上
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
//请定义APP_NAME 常量 
if(!defined("APP_NAME")) exit("Please define constants APP_NAME！");

//框架路径 
//define("IFRAME_ROOT",dirname(__file__).DIRECTORY_SEPARATOR);
define("IFRAME_ROOT",$_SERVER['DOCUMENT_ROOT'].dirname($_SERVER["SCRIPT_NAME"]).'/iframe/');    //因为Linux系统软链接的目录必须使用这种方式
//var_dump($_SERVER['DOCUMENT_ROOT']);
//var_dump(IFRAME_ROOT); 
//应用开发路径
define("APP_PATH",IFRAME_ROOT."../".APP_NAME.DIRECTORY_SEPARATOR);
//调式开关
defined('APP_DEBUG') or define('APP_DEBUG',true); // 是否开启模式
//默认显示错误
ini_set('display_errors', 0);
//日志记录开关
define('LOG',false);
//缓存开关
define('CACHE',false);
//版本信息
define('IFRAME_VERSION', '1.0.0');

//框架的一些路径常量 
defined('ICORE') or define('ICORE',  IFRAME_ROOT.'core/');
defined('ILIB') or define('ILIB',  IFRAME_ROOT.'lib/');
defined('IVIEW') or define('IVIEW',  IFRAME_ROOT.'views/');
//defined('ICONF') or define('ICONF',  IFRAME_ROOT.'config/');
defined('ICONF') or define('ICONF',  dirname(IFRAME_ROOT).'/config/');

//应用的常用路径 
defined('CONFIG_PATH_NAME') or define('CONFIG_PATH_NAME',  'config');    //配置 文件
define('CONF_PATH',  APP_PATH.CONFIG_PATH_NAME.DIRECTORY_SEPARATOR);    //配置 文件

defined('CONTROLLER_PATH_NAME') or define('CONTROLLER_PATH_NAME',  'controllers');    //控制器
define('CONT_PATH',  APP_PATH.CONTROLLER_PATH_NAME.DIRECTORY_SEPARATOR);   //控制器

defined('MODEL_PATH') or define('MODEL_PATH',  APP_PATH.'models/');   //模型

defined('VIEW_PATH_NAME') or define('VIEW_PATH_NAME',  'views');    //视图文件路径
defined('VIEW_PATH') or define('VIEW_PATH',  APP_PATH.VIEW_PATH_NAME.DIRECTORY_SEPARATOR);    //视图文件路径

defined('CACHE_PATH') or define('CACHE_PATH',  APP_PATH.'cache/');     //缓存
defined('LANG_PATH') or define('LANG_PATH',  APP_PATH.'lang/');     //语言包

 //应用语言环境
defined('LANG') or define('LANG', 'zh_cn'); 
 //时区
defined('TIMEZONE') or define('TIMEZONE', 'Asia/Shanghai'); 

//网站根目录
$_root_ = dirname($_SERVER["SCRIPT_NAME"]);
$_root = str_replace('\\', '/', $_root_);

if(substr($_root, -1,1) != '/'):
    $_root.='/';
endif;

defined('__ROOT__') or define('__ROOT__',  $_root);  
define('__URL__', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);

//生成应用目录结构
function createAppDir(){
    //
    if(!is_dir(APP_PATH)) mkdir(APP_PATH,0755,true);
    if(is_writeable(APP_PATH)) {
        $dirs  = array(
            CONT_PATH,
			CONF_PATH,
            MODEL_PATH,
            VIEW_PATH,
            CACHE_PATH,
            LANG_PATH,
            );
        foreach ($dirs as $dir){
            if(!is_dir($dir))  mkdir($dir,0755,true);
        }
        
        //项目路由规则
        if(!is_file(CONF_PATH.'route.php'))
            file_put_contents(CONF_PATH.'route.php',"<?php\n//判断是否开启 rewrite 模式 ，\nif(URL_REWRITE){\n\t//'array('正则表达式'，'    控制器/操作')'\n\t    return ;\n}else{\n\n\t//'array('正则表达式'，'控制器/操作')'\n\treturn array(\n\t\n\t);\n}\n?>");
			
        //index  控制器
        if( !is_file(CONT_PATH.'indexController.php')  ){
            file_put_contents(CONT_PATH.'indexController.php',"<?php \n\n namespace controllers; \n\n class indexController extends \core\Controller { \n\n \t public function index(){ \n \t\t echo '<html><body style=\'text-align:center; color:#555;margin-top:120px;\' ><h1>欢迎使用 iframe 框架</h1><p>@长沙异新优网络科技有限责任公司</p></body></html>'; \n\t } \n\n } \n ?>");
        }
    }else{
        header('Content-Type:text/html; charset=utf-8');
        //目录不可写，应用目录无法自动生成。
        exit('error:'.APP_PATH.' cannot write, directory cannot be automatically generated！<BR>');
    }
}

//加载系统基础函数库
require ICORE.'common.php';
//加载iframe类
require ICORE.'iframe.php';
 //加载公共函数库
 if(is_file(IFRAME_ROOT.'PublicFunctionLibrary.php')){
     require IFRAME_ROOT.'PublicFunctionLibrary.php';
 }
    
createAppDir();

//检测项目中是否有定义的常量 ，如果 有则加载 
if( file_exists(CONF_PATH.'const.php') ){
    require_once CONF_PATH.'const.php';
}else if( file_exists(ICONF.'const.php') ){//否则从框架加载
    require_once ICONF.'const.php';
}

//URL重写，默认为关闭 ，主要是匹配 路由时会用到。
defined('URL_REWRITE') or define('URL_REWRITE',  false);

header('X-Powered-By: Author:xianglu 316686606@qq.com ');
