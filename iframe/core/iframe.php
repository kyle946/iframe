<?php

/**
 * iframe 类
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */
class iframe {

    static public function start() {
//        error_reporting(0);
        // 设定错误和异常处理
        register_shutdown_function(array('iframe', 'fatalError'));
        set_error_handler(array('iframe', 'Error'));
        set_exception_handler(array('iframe', 'Exception'));
        // 注册AUTOLOAD方法
        session_start();
        spl_autoload_register('iframe::_autoload');
        //加载项目的时区
        date_default_timezone_set(TIMEZONE);
        self::init();
//        self::judgeFormpost();
        self::exec();
    }

    /**
     * 致命错误捕获
     */
    static public function fatalError() {
        if ($e = error_get_last()) {
            iframe::Error($e['type'], $e['message'], $e['file'], $e['line']);
        }
    }

    static public function Error($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }
        $html = '';
        switch ($errno) {
            case E_USER_ERROR:
                $html.= "<b>ERROR($errno):</b> $errstr<br />\n";
                $html.= "  Fatal error on line $errline in file $errfile";
                $html.= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                $html.= "Aborting...<br />\n";
                break;
            case E_USER_WARNING:
                $html.= "<b>WARNING($errno):</b> $errstr<br />\n";
                break;
            case E_USER_NOTICE:
                $html.= "<b>NOTICE($errno):</b> $errstr<br />\n";
                break;
            case E_WARNING:
                $html.="<b>WARNING($errno):</b> $errstr<br />\n";
                break;
            case E_NOTICE:
                $html.="<b>NOTICE($errno):</b> $errstr<br /> $errfile:$errline<br />\n";
                break;
            default:
                $html.= "Unknown error type: [$errno] $errstr <br />$errfile:$errline<br />\n";
                break;
        }
        if( $_SERVER["SCRIPT_NAME"]=='/cashier.php' ){    //如果是收银终端 接口的报错
            echo json_encode(array('status'=>0,'tips'=>$html));
            exit();
        }else{
            msg('系统异常！<br />' . $html);
        }
    }

    static public function Exception($exception) {
        if ($_SERVER["SCRIPT_NAME"] == '/cashier.php') {    //如果是收银终端 接口的报错
            echo json_encode(array('status' => 0, 'tips' => $exception->getMessage() ) );
            exit();
        } else {
            msg($exception->getMessage());
            exit();
        }
    }

    /**
     * 判断form表单的POST提交
     */
    static public function judgeFormpost() {
        //判断 POST提交表单验证    start
        if ($_SERVER["REQUEST_METHOD"] == 'POST' && cfg('FORM_POST_CHECK') == true) {
            $verifykey = false;  //用来判断验证是否通过
            //$redisKeyName = md5($_SERVER["REQUEST_URI"] . session_id());  //保存验证KEY的数组的redis KEY名称
            $redisKeyName = md5(session_id());
            $redis = new \Redis();
            if (!defined('REDIS_ADDR') || !defined('REDIS_PORT')) {  //判断REDIS是否定义
                msg('redis服务器未定义');
            }
            $redisConnectRes = $redis->connect(REDIS_ADDR, REDIS_PORT);
            if (!$redisConnectRes) {
                msg('redis服务器未定义');
            } //如果REDIS连接失败
            $arr = json_decode($redis->get(REDIS_PRE . $redisKeyName), 1);  //从redis取出验证KEY的数组
            $formkeyname = null;
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    if (@isset($_POST[$v])) {
                        $formkeyname = $v;
                        break;
                    }
                }
            }
            
            //特定的控制器和方法允许不验证   begin
            if(  (CONTROLLER_NAME=='image' && ACTION_NAME=='upload')  ){//如是后台图片上传
                $verifykey=true;
            }
            if( CONTROLLER_NAME=='jincheng' ){
                $verifykey=true;
            }
            //  end
            
            //如果已经取出了key的名称
            if ($formkeyname) {
                //根据 key 名称 取出值
                $v1 = $redis->get(REDIS_PRE . $formkeyname);
                $redis->delete(REDIS_PRE . $formkeyname);  //获取后删除
                //取出表单中的值
                $v2 = $_POST[$formkeyname];
                if (md5($v1) == $v2) {
                    $verifykey = true;
                    unset($_POST[$formkeyname]);
                }
            }
            if (!$verifykey) {
                if (judgeAjaxRequest()) {
                    echo json_encode(array('status' => 0));
                    exit();
                }
                msg('页面已过期，请 <a href="./" >刷新</a> 页面重新提交！');
                exit();
            }
        }
        //判断 POST提交表单验证    end
    }

    static public function init() {
        //加载框架核心文件
        $list = array(
            ICORE . 'Controller.php',
            ICORE . 'url.php',
        );
        foreach ($list as $value) {
            if (is_file($value))
                require $value;
        }

        $var_controller = cfg('VAR_CONTROLLER');
        $var_action = cfg('VAR_ACTION');

        /**
         * ----- start -----
         * 获取CONTROLLER _NAME 和 ACTION_NAME 
         */
        //匹配路由规则
        \core\url::_route();

        //如果在路由规则中没有发现定义的指定的控制器和方法，则获取URL中的操作和方法
        if (!defined('CONTROLLER_NAME')) {
            if (\core\url::_get($var_controller)):
                define('CONTROLLER_NAME', \core\url::_get($var_controller));
            else:
                if (defined('CONTROLLER_NAME_DEFAULT')) {
                    define('CONTROLLER_NAME', CONTROLLER_NAME_DEFAULT);
                } else {
                    //如果没有指定Controller，则获取配置文件指定的默认Controller
                    $default_controller = cfg('DEFAULT_CONTROLLER');
                    define('CONTROLLER_NAME', $default_controller);
                }
            endif;
        }

        if (!defined('ACTION_NAME')) {
            if (\core\url::_get($var_action)):
                define('ACTION_NAME', \core\url::_get($var_action));
            else:
                //如果没有指定action，则获取配置文件指定的默认action
                $default_action = cfg('DEFAULT_ACTION');
                define('ACTION_NAME', $default_action);
            endif;
        }
        /**
         * ----- end -----
         */
    }

    static public function exec() {
        $classFile = "\\" . CONTROLLER_PATH_NAME . "\\" . CONTROLLER_NAME . 'Controller';
        //如果类文件不存在！
        if (!class_exists($classFile)) {
            msg("访问方式错误，请确认链接地址是否正确！ 1001");
        }
        $class_ = new $classFile;
        $class = new \ReflectionClass($classFile);
        if ($class->hasMethod(ACTION_NAME)) {

            $before = $class->getMethod(ACTION_NAME);
            if ($before->isPublic()) {
                $arr = array_merge(\core\url::_get(), $_REQUEST);
                $before->invoke($class_,$arr);
            }
        } else {
            //如果指定的方法不存在则执行模块的空方法
            $action_name = cfg('EMPTY_ACTION');
            $before = $class->getMethod($action_name);
            if ($before->isPublic()) {
                $arr = array_merge(\core\url::_get(), $_REQUEST);
                $before->invoke($class_,$arr);
            }
        }
    }

    static public function _autoload($className) {
        $class = str_replace('\\', '/', $className);
        $controllersFile = APP_PATH . $class . '.php';
        if (is_file($controllersFile)) {
            require $controllersFile;
            return true;
        } else {
            $controllersFile = IFRAME_ROOT . $class . '.php';
            if (is_file($controllersFile)) {
                require $controllersFile;
                return true;
            } else {
                return false;
            }
        }
    }

}
