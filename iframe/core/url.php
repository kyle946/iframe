<?php

/**
 * Description of url
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace core;

class url {

    //是否匹配到路由规则
    static protected $isroute = null;

    /**
     * 判断是否开启了rewrite模式 ，返回处理后的 QUERY_STRING
     * @return string
     */
    static public function _querystring(&$urlrewrite = 0) {
        $urlvar = cfg('VAR_PATHINFO');
        $urldata = isset($_REQUEST[$urlvar])?$_REQUEST[$urlvar]:null;
        if (!empty($urldata) && strstr($urldata, '/')) {
            $urlrewrite = 1;
            //检测前面有没有 / 符号，有就删除
            if (substr($_GET[$urlvar], 0, 1) == '/'):
                return substr($_GET[$urlvar], 1);  //去掉前面的 / 符号
            else:
                return $_GET[$urlvar];
            endif;
        } else {
            return $_SERVER['QUERY_STRING'];
        }
    }

    /**
     * 匹配路由规则
     */
    static public function _route() {
        $route = include CONF_PATH . 'route.php';
        $urlvar = url::_querystring();
        $action = null;
        if ( is_array($route) && !empty($urlvar) ) {
            foreach ($route as $key => $value) {
                if (preg_match($value[0], $urlvar)) {
                    $action = $value[1];
                }
            }
            if (!empty($action)):
                list($className, $actionName) = explode('/', $action);
                define('CONTROLLER_NAME', $className);
                define('ACTION_NAME', $actionName);
                self::$isroute = $action;
            endif;
        }
    }

    /**
     * 判断是否开启了rewrite模式 ，返回处理后的$_GET数组 
     * @param string $name  $_GET 数组 键名
     * @return array
     */
    static public function _get($name = null) {

        $urlrewrite = 0;  //下面这行是按址传递，所以会改变 $urlrewrite 变量的值，并不是默认的  0
        $queryString = self::_querystring($urlrewrite);

        //如果开启了rewrite模式  并且 rewrite模式下 VAR_PATHINFO 参数不为空
        if ( $urlrewrite && $queryString ) {

            //检测有没有后缀，如果有再裁剪
            if (strstr($queryString, cfg('URL_SUFFIX'))) {
                //去掉后缀
                $suffixlen_ = strlen(cfg('URL_SUFFIX'));
                $suffixlen = $suffixlen_ - $suffixlen_ * 2;
                $queryString = substr($queryString, 0, $suffixlen);
            }

            //如果匹配到了路由规则，则加上控制器和操作。
            if (!empty(self::$isroute)) {
                $queryString = self::$isroute . '/' . $queryString;
            }

            $array = explode('/', $queryString);
            //如果没有 / 符号，只有一个参数的情况 下，手动添加第二个为 index
            if (count($array) == 1) {
                $array[1] = 'index';
            }

            if (!empty($queryString) && is_array($array)) {
                //取出模块和操作
                $var_controller = cfg('VAR_CONTROLLER');
                $var_action = cfg('VAR_ACTION');
                $arr[$var_controller] = $array[0];
                $arr[$var_action] = $array[1];
                unset($array[0]);
                unset($array[1]);
                //判断URL是不是使用了  /i/m/id_16.html  模式 ，如果 是则把最后一个字符串用 _ 切割成数组
                //使用这种形式是为了不产生太深的目录 路径 ，对搜索引擎不利
                if ( count($array) == 1 ) {
                    $varstr = end($array);
                    $array = null;
                    $array = explode('_', $varstr);
                } elseif (count($array) == 3) {
                    $varstr = end($array);
                    $key_ = key($array);
                    unset($array[$key_]);
                    $array_ = explode('_', $varstr);
                    $array = array_merge($array, $array_);
                }
//                    var_dump($array);
                //其它的用作变量
                if (!empty($array)) {
                    $arr1 = array();
                    $arr2 = array();
                    foreach ($array as $key => $value) {
                        if ((int) $key % 2 == 0):
                            $arr1[] = $value;
                        else:
                            $arr2[] = $value;
                        endif;
                    }

                    foreach ($arr1 as $key => $value) {
                        if (!empty($arr2[$key])) {
                            $arr[$value] = $arr2[$key];
                        } else {
                            $arr[$value] = null;
                        }
                    }
                }
                if (empty($name)) {
                    return $arr;
                } else {
                    if (empty($arr[$name]))
                        return false;
                    return $arr[$name];
                }
            }else {
                return false;
            }
        } else {
            if (empty($name)) {
                return $_GET;
            } else {
                if (empty($_GET[$name]))
                    return false;
                return $_GET[$name];
            }
        }
    }

}
