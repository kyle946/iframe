<?php

/**
 * Description of Controller
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace core;
class Controller {
    protected $view = null;

    
    public function __construct() {
        $this->view = new view();
    }

    /**
     * 模板变量赋值
     * @param type $name
     * @param type $value
     */
    public function assign($name,$value = '') {
        $this->view->assign($name, $value);
    }
    
    public function display($templateFile = '') {
        if(empty($templateFile)){
            $templateFile = ACTION_NAME;
        }
        $this->view->display($templateFile);
    }
    public function show($templateFile = '') {
        if(empty($templateFile)){
            $templateFile = ACTION_NAME;
        }
        $this->view->display($templateFile);
    }
    
    /**
     * 经过  strip_tags 和 trim 处理后的post变量 
     * @param type $var
     * @return type
     */
    public function _post($var) {
        if( !isset($_POST[$var]) ){
            return false;
        }
        return strip_tags(trim($_POST[$var]));
    }
    
    /**
     * 经过  strip_tags 和 trim 处理后的get变量 
     * @param type $var
     * @return type
     */
    public function _get($var) {
        if( !isset($_GET[$var]) ){
            return false;
        }
        return strip_tags(trim($_GET[$var]));
    }
    
    //开启伪静态下的get
    public function rget($var = null) {
        return \core\url::_get($var);
    }
    
    /**
     * 更新 表单验证
     * @return type
     */
    public function formverify() {
            $formverifykey = cfg('FORM_KEY');
            $URL = $_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"];
            $keyvalue = rand(100000, 999999).$URL;
            if(isset($_SESSION[$formverifykey]) && is_array($_SESSION[$formverifykey])){
                $_SESSION[$formverifykey] = array_merge(array($keyvalue),$_SESSION[$formverifykey]);
            }else{
                $_SESSION[$formverifykey] = array($keyvalue);
            }
            return md5($keyvalue);
    }
    
    public function _empty() {
//        throw new \Exception('访问方式错误，请确认链接地址是否正确！');
        msg("访问方式错误，请确认链接地址是否正确！ 1002");
    }
}
