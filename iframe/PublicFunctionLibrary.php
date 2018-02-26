<?php

//加载公共函数库

/**
 * 判断是不是首页
 * @return boolean
 */
function isindex() {
    if (CONTROLLER_NAME == 'index' and ACTION_NAME == 'index') {
        return true;
    } else {
        false;
    }
}

/**
 * 打印 颜色 值
 * @return type
 */
function printColor() {
    $r = rand(150, 250);
    $g = rand(150, 250);
    $b = rand(10, 190);
    return "rgb($r,$g,$b)";
}

/**
 * 截取字符
 * @param type $str
 * @param type $int
 * @return type
 */
function ysubstr($str, $int = 15) {
    $s = null;
    if (mb_strlen($str, 'utf-8') > $int)
        $s = '…';
    return mb_substr($str, 0, $int, 'utf-8') . $s;
}

/**
 * 处理url
 * @param type $url
 * @return type
 */
function u($url) {
    //如果 URL 的最后面是 / 或者 _ 则删除
    if (substr($url, -1) === '/' || substr($url, -1) === '_') {
        $url = substr($url, 0, -1);
    }
    $template_suffix = cfg('template_suffix');  //取伪静态文件后缀
    return $url . $template_suffix;
}

/**
 * 商品列表页专用 ，取属性值解析给url 使用
 * @param type $key
 */
function goodsurlattr($data, $k = null) {
    if (!empty($k))
        unset($data[$k]);
    $str = null;
    foreach ($data as $key => $value) {
        if (!empty($value))
            $str .= $key . '_' . $value . '_';
    }
    return $str;
}

/**
 * 用户登录 判断
 * @return string
 */
function loginJudbe() {
    //如果 是在手机上登录 另作判断 
    if (judgeMobileBrowse() == true) {
        if (isset($_COOKIE['author_316686606'])) {
            /*
             * 这里为什么要把空格替换成加号：
             * 对于通过application/x-www-form-urlencoded的HTTP传递值，+会被自动替换为空格。所以也有各种对base64编码进行扩充的，比如对+,/等符号进行替换的
             */
            $str = str_replace(' ', '+', $_COOKIE['author_316686606']);
            $userinfo = unserialize(base64_decode($str));
            if (is_array($userinfo)) {
                return $userinfo;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    if (!isset($_SESSION['userInfo']) or empty($_SESSION['userInfo']) or ! is_array($_SESSION['userInfo'])) {
//    if( !isset($_SESSION['userInfo']) or empty($_SESSION['userInfo']) or !is_array($_SESSION['userInfo']) or $_SESSION['userInfo']['expiretime'] < time() ){
        if (isset($_SESSION['userInfo']))
            unset($_SESSION['userInfo']);
        return false;
    }else {
//        if( time()+120 > (int)$_SESSION['userInfo']['expiretime'] ){
//            $_SESSION['userInfo']['expiretime'] = time() + 120;
//        } 
        return $_SESSION['userInfo'];
    }
}

/**
 * 业务员登录判断
 * @return boolean
 */
function clerk_login_judbe() {
    //如果 是在手机上登录 另作判断 
    if (judgeMobileBrowse() == true) {
        if (isset($_COOKIE['clerk_info'])) {
            /*
             * 这里为什么要把空格替换成加号：
             * 对于通过application/x-www-form-urlencoded的HTTP传递值，+会被自动替换为空格。所以也有各种对base64编码进行扩充的，比如对+,/等符号进行替换的
             */
            $str = str_replace(' ', '+', $_COOKIE['clerk_info']);
            $clerk_info = unserialize(base64_decode($str));
            if (is_array($clerk_info)) {
                return $clerk_info;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    if (!isset($_SESSION['clerk_info']) or empty($_SESSION['clerk_info']) or ! is_array($_SESSION['clerk_info'])) {
//    if( !isset($_SESSION['userInfo']) or empty($_SESSION['userInfo']) or !is_array($_SESSION['userInfo']) or $_SESSION['userInfo']['expiretime'] < time() ){
        if (isset($_SESSION['clerk_info']))
            unset($_SESSION['clerk_info']);
        return false;
    }else {
        return $_SESSION['clerk_info'];
    }
}

/**
 * 输出消息。
 * @param type $content
 */
function message($content, $title = 'error') {
    $model = new \core\Controller();
    if (isset($_SESSION['userInfo']) and ! empty($_SESSION['userInfo'])) {
        $model->assign('userinfo', $_SESSION['userInfo']);
    }
    $model->assign('title', $title);
    $model->assign('messageContent', $content);
    $model->display('other/message');
    exit();
}

/**
 * 从快递100 获取快递的物流信息 
 * @param int $id  快递单号
 * @param string $com  快递公司编码代号
 * @return array 返回PHP数组
 */
function getkuaidi($id, $com) {
    $data = gethtml("www.kuaidi100.com", "http://www.kuaidi100.com/query?type=$com&postid=$id&id=&valicode=");
    return json_decode($data, true);
}

/**
 * 返回一个栏目下所有子栏目 
 * @param type $id  父栏目ID
 * @param type $data   从数据库查出的栏目数据
 * @param type $arr   写入的数组数据
 */
function returnColumnSon($id = 0, $data, &$arr) {
    foreach ($data as $key => $value) {
        if ($value['parentId'] == $id) {
            $arr[] = $value['id'];
            returnColumnSon($value['id'], $data, $arr);
        }
    }
}

/**
 * 替换栏目类型的数字为指定字符
 * @param type $typeid
 * @return string
 */
function replaceColumnType($typeid = null) {
    $str = 'i';
    switch ((int) $typeid) {
        case 1:
            $str = 'i';
            break;
        case 2:
            $str = 'i';
            break;
        case 3:
            $str = 'c';
            break;
        case 4:
            $str = 'g';
            break;
        default:
            break;
    }
    return $str;
}

function searchColumnSpeStyle($id = null) {
    if (!isset($GLOBALS['CurColumnId']) || count($GLOBALS['CurColumnId']) == 0 || empty($id)):
        return false;
    endif;
    if (array_search($id, $GLOBALS['CurColumnId']) !== FALSE) {
        return true;
    }
}

/**
 * 微商城 商品详情页面的图片替换 
 */
function regeximage1($content) {
    $content = str_replace(array('800_', '400_'), 'thumb_', $content);
    return "<img src='/static/default.gif' xsrc='$content' init='1' ";
}

/**
 * 
 * @param type $src  12100  被替换的字符串
 * @param type $i1    2   替换第几位
 * @param type $re    3   替换成什么 ？
 * 
 * @return str    13100
 */
function replaceStr($src = 'abcdef', $i1 = 3, $re = '3') {
    $i = 1;
    $res = null;
    foreach (str_split($src) as $k => $v) {
        if ($i == $i1) {
            $v = $re;
        }
        $res = $res . $v;
        $i++;
    }
    return $res;
}

/**
 * 根据栏目ID或标签 获取 模型标签名 ，也就是表名
 * @param type $id
 * @return array  数组 ，栏目id，栏目名称，模型名称
 */
function getArticletable($id = null) {
    if ($id) {
        $m = new \core\model();
        $sql = "select c.id,c.name,c.tplList,c.tplArticle,c.tplContent,m.mark from `" . SQLPRE . "column` c left join `" . SQLPRE . "models` m on c.model=m.id where c.id='$id' or c.mark='$id'";
        $info = $m->find($sql);
        if ($info) {
            return $info;
        }
    }
    return false;
}

/**
 * 查询文章评论状态 
 * @param type $id  文章ID
 * @param type $table   文章表名
 * @return bool  true  为已经 关闭评论, false  为未关闭或者参数错误
 */
function getartcommentstatus($id = null, $table = null) {

    if ($id && $table) {
        $m = new \core\model();
        //查询文章是不是已经禁止评论  start
        $sql = "select arcrank from `" . SQLPRE . "$table` where id=$id";
        $arcrank = $m->field($sql);
        if ($arcrank == 'oc') {
            return 1;
        } else {
            return false;
        }
        //查询文章是不是已经禁止评论  end
    } else {
        return false;
    }
}

function get_goods_cate($len = 5) {
    $arr = array();
    $m = new core\model();
    $arr = $m->select("select id,name from `" . SQLPRE . "category` where status=1 and level=1 order by sort desc,id desc limit 5");
    return $arr;
}




/**
 * 发送注册验证短信
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgReg($mobile = null) {
    if (empty($mobile)) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->select('select mark as yixinu,val from ' . SQLPRE . 'shop_config where mark like "alidayu%"');
        if ($config['alidayusmssendstatus']['val'] == '2') {
            return array('msg' => '短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate1']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname1']['val'];
        //配置 end  ########################################

        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsRegCode'] = md5($code);
        $SmsParam = array(
            'code' => (string) $code,
            'product' => '[异新优商城内容系统]'
        );

        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `" . SQLPRE . "sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->field($sql);
        if ($sendtime) {
            return array('msg' => '发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '注册验证:' . $code;
        $m->save($data, 'sendsmslog');
        //保存发送记录  end
        //如果开启了调试模式 ， 直接 返回 1
        if ($config['alidayusmssendstatus']['val'] == '3') {
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string) $mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换
//        return 1;

        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if (isset($sendres['result'])) {
            return 1;
        } else {
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg'] = '发送太频繁或已经超限制，请稍后再试！';
                    break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg'] = '手机号码格式错误！';
                    break;
            }
            return $sendres;
        }
    }
}

/**
 * 发送修改密码验证短信
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgChpwd($mobile = null) {
    if (empty($mobile)) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->select('select mark as yixinu,val from ' . SQLPRE . 'shop_config where mark like "alidayu%"');
        if ($config['alidayusmssendstatus']['val'] == '2') {
            return array('msg' => '短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate2']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname2']['val'];
        //配置 end  ########################################

        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsRegCodeChpwd'] = md5($code);
        $SmsParam = array(
            'code' => (string) $code,
            'product' => '[异新优商城内容系统]'
        );

        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `" . SQLPRE . "sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->field($sql);
        if ($sendtime) {
            return array('msg' => '发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '修改密码:' . $code;
        $m->save($data, 'sendsmslog');
        //保存发送记录  end
        //如果开启了调试模式 ， 直接 返回 1
        if ($config['alidayusmssendstatus']['val'] == '3') {
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string) $mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换

        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if (isset($sendres['result'])) {
            return 1;
        } else {
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg'] = '发送太频繁或已经超限制，请稍后再试！';
                    break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg'] = '手机号码格式错误！';
                    break;
            }
            return $sendres;
        }
    }
}

/**
 * 发送验证短信  变更验证
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgMod($mobile = null) {
    if (empty($mobile)) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->select('select mark as yixinu,val from ' . SQLPRE . 'shop_config where mark like "alidayu%"');
        if ($config['alidayusmssendstatus']['val'] == '2') {
            return array('msg' => '短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate3']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname3']['val'];
        //配置 end  ########################################

        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsModcode'] = md5($code);
        $SmsParam = array(
            'code' => (string) $code,
            'product' => '[异新优商城内容系统]'
        );

        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `" . SQLPRE . "sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->field($sql);
        if ($sendtime) {
            return array('msg' => '发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '身份验证:' . $code;
        $m->save($data, 'sendsmslog');
        //保存发送记录  end
        //如果开启了调试模式 ， 直接 返回 1
        if ($config['alidayusmssendstatus']['val'] == '3') {
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string) $mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换

        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if (isset($sendres['result'])) {
            return 1;
        } else {
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg'] = '发送太频繁或已经超限制，请稍后再试！';
                    break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg'] = '手机号码格式错误！';
                    break;
            }
            return $sendres;
        }
    }
}

/**
 * 取微信配置
 * @return type
 */
function get_weixin_config() {
    $m = new \core\model();
    $sql = "select val from `" . SQLPRE . "shop_config` where mark='weixinappid' ";
    $appid = $m->field($sql);
    $sql = "select val from `" . SQLPRE . "shop_config` where mark='weixinsecret' ";
    $secret = $m->field($sql);
    return array('appid' => $appid, 'secret' => $secret);
}

/**
 * 微信登录，获取 OPENID
 * @param string $return_url  跳转到哪个地址
 * @return type
 */
function getOpenid($return_url = null) {
    if (isset($_COOKIE ['weixin_user_openid']) && !empty($_COOKIE ['weixin_user_openid'])) {
        return $_COOKIE ['weixin_user_openid'];
    }
    $config = get_weixin_config();
    //微信回调地址
    if (!$return_url) {
        $return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    //$login_return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $config['appid'] . "&redirect_uri=" . urlencode($return_url) . "&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
    header("location:$oauth2_code");
    exit();
}

/**
 * 微信登录，获取OPENID回调地址的方法
 */
function wx_oauth() {
    if (isset($_COOKIE ['weixin_user_openid']) && !empty($_COOKIE ['weixin_user_openid'])) {
        return $_COOKIE ['weixin_user_openid'];
    }
    if (isset($_REQUEST ['code'])) {
        $config = get_weixin_config();
        $state = $_REQUEST ['state'];
        $code = $_REQUEST ['code'];
        $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $config['appid'] . "&secret=" . $config['secret'] . "&code=" . $code . "&grant_type=authorization_code";
        $content = file_get_contents($oauth2_code);
        $token = @json_decode($content, true);
        if (empty($token) || !is_array($token) || empty($token ['access_token']) || empty($token ['openid'])) {
            msg('获取微信公众号授权' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: ' . $content);
        }
        $openid = $token ['openid'];
        setcookie('weixin_user_openid', $openid, time() + 7200);
        return $openid;
    }
}

/**
 * 微信登录，取用户信息
 */
function getWxuserInfo($openid) {
    require_once IFRAME_ROOT . '/api/jssdk.php';
    $config = get_weixin_config();
    $jssdk = new JSSDK($config['appid'], $config['secret']);
//    throw new \Exception($openid);
    $accessToken = $jssdk->getAccessToken();
    $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN";
    $content = file_get_contents($oauth2_url);
    $info = @json_decode($content, true);
    //如果是本地调试
//    if ( islocalhost() ) {
//        $string = '{"subscribe":1,"openid":"o_mSyt2m8pP8OvyF-yEPXd-cjo11","nickname":"\u56db\u56cd","sex":1,"language":"zh_CN","city":"\u957f\u6c99","province":"\u6e56\u5357","country":"\u4e2d\u56fd","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/b2ONlmmVZRrCbLrdGoS2FO2xeucVd28ibXMw8icic3ym7B8iaKCOjxFtWIn2MI0UcIrrDQ8qW38k5gTJLr7r79ZvOY3Z6MSzgJ17\/0","subscribe_time":1480405448,"remark":"","groupid":0,"tagid_list":[]}';
//        return json_decode($string, 1);
//    }
    return $info;
}

/**
 * 根据地址称名获取编号
 * @param type $proviceSn
 * @param type $citySn
 * @param type $countySn
 * @return type
 */
function get_addres_sn($provice, $city, $county, $m = null) {
    if (!$m)
        $m = new \core\model();
    $data = array();
    $data['provice'] = $m->field("select provice_id  from " . $m->prefix . "area_provice  where provice_name like '%$provice%' ");
    $data['city'] = $m->field("select city_id  from " . $m->prefix . "area_city  where city_name like '%$city%' ");
    $data['county'] = $m->field("select county_id  from " . $m->prefix . "area_county  where county_name like '%$county%' ");
    return $data;
}

/**
 * 拆分订单
 */
function split_order() {
    
}

/**
 * 入库, 指定一组商品入库
 * 相关表: purchase_order , _inventory_records , purchase_order_goods 
 * 
 * @param array $data 入库订单数据
 * @param array $records_data 出入库记录数据
 * @param string $records_data['reason'] 出入库原因 , 1为采购入库，2为下单出库，3损耗出库，4为调拔出库，5为调拔入库
 * @param string $records_data['comments']  备注
 * @param array $goods 商品数据
 */
function increase_inventory_to_specify_goods($data = array(), $records_data = array(), $goods = array(), $m = null) {
    if (!$m)
        $m = new \core\model();
    $data['status'] = @$data['status'] ?: 1;    //3未采购 ,2已采购未到货 ,1已到货 
//    $data['type'];  //类型： 1采购，2调拨
//    $data['principal']; //采购负责人
    $data['date'] = @$data['date'] ?: date('Y-m-d H:i');  //订单生成时间
//    $data['comments'];  //说明
//    $data['warehouse_id'];  //仓库ID
//    $data['warehouse_name']; //仓库名称
    $data['storage_time'] = @$data['storage_time'] ?: date('Y-m-d H:i');  //入库时间
    //写入仓库标签
    if (isset($data['warehouse_id'])) {
        $data['warehouse_mark'] = $m->field("select mark from `" . SQLPRE . "stores` where id='$data[warehouse_id]'");
    }
    $order_id = $m->save($data, 'purchase_order');  //添加入库订单

    if (is_array($goods) && count($goods) > 0) {
        $total_price = 0;
        //保存商品数据
        foreach ($goods as $goods_id => $item) {

            $tmpvar = $m->find("select * from `" . SQLPRE . "purchase_order_goods` where goods_id=$goods_id order by id desc");
            $data_goods['purchase_order_id'] = $order_id;
            $data_goods['goods_id'] = $goods_id;
            $data_goods['goods_sn'] = $item['goods_sn'];
            $data_goods['goods_name'] = $item['goods_name'];
//                    $data_goods['supplier_id'] = $_REQUEST['supplier_id'];
            $data_goods['package'] = @$item['package'] ?: $tmpvar['package'];
            $data_goods['pack_spec'] = @$item['pack_spec'] ?: $tmpvar['pack_spec'];
            $data_goods['nums'] = $item['nums'];
            $data_goods['purch_price'] = @$item['purch_price'] ?: $tmpvar['purch_price'];
            $data_goods['price_total'] = @$item['price_total'] ?: $tmpvar['purch_price'] * $item['nums'];
            $data_goods['warehouse_mark'] = $data['warehouse_mark'];

            $data_goods['production_date'] = $tmpvar['production_date'];
            $data_goods['shelfdate'] = $tmpvar['shelfdate'];
            $data_goods['expire'] = $tmpvar['expire'];

            $data_goods['status'] = $data['status'] ?: 1;

            //入库订单总价格
            $total_price = $total_price + round($data_goods['price_total'], 2);
            $m->save($data_goods, 'purchase_order_goods');

            //入库增加商品总库存
            $m->save(array("numbers" => "`numbers`+$data_goods[nums]---"), 'goods_additional', 'u', "id=$goods_id");

            //入库记录
            $m->save(
                    array(
                'goods_id' => $goods_id,
                'goods_name' => $data_goods['goods_name'],
                'warehouse_id' => $data['warehouse_id'],
                'warehouse_name' => $data['warehouse_name'],
                'type' => 1, //入库
                'nums' => $data_goods['nums'],
                'reason' => $records_data['reason'],
                'comments' => $records_data['comments'],
                'var' => $order_id
                    ), 'inventory_records');
        }
        //更新订单的总价格
        $m->save(array('total' => $total_price), 'purchase_order', 'u', "id=$order_id");

        //如是是入库，入 库后更新商品缓存
        up_goods_cache();
    }
}

/**
 * 获取商品的实际库存
 * 
 * @param type $goodsid
 * @return int
 */
function get_goods_store($goodsid) {
    $m = new \core\model();
    $redis = new \models\yredis();
    //业务员的编号
    $clerk = clerk_login_judbe();
    //默认选  warehouse1 仓库发货 
    $store_info = $m->find("select * from " . SQLPRE . "stores where mark='warehouse1'");
    //如果是业务员登录 ，则从业务员的仓库发货
    if ($clerk) {
        $store_info_2 = $m->find("select * from " . SQLPRE . "stores where mark='$clerk[id]'");
        //只有业务员的仓库信息存在，才能从业务员的仓库发货
        if ($store_info_2) {
            $store_info = $store_info_2;
        }
    }
    $store_id = $store_info['id'];
    $stores_goods_num = $redis->get(REDIS_PRE . "stores_goods_" . $store_id . "_" . $goodsid);
    return intval($stores_goods_num);
}

/**
 * 判断有没有特殊字符 
 * @param type $string
 */
function filter_string($string) {
    $bool = preg_match("/^(.*?)[\<|\>|\=|\/|\.|\?|\+|\'|\!|\@|\#|\$|\%|\*](.*?)$/", $string);
    if ($bool) {
        return false;
    } else {
        return true;
    }
}

/**
 * 商户接口验证，经销版所有商户验证
 * @param type $mchid   商户ID
 * @param type $rand    随机字符串
 * @param type $sign    传入的sign
 * @return boolean
 */
function mch_verify($mchid = 0, $rand = null, $sign = null) {
    if ($mchid) {

        $data['mchid'] = $mchid;

        //begin 记录每个业务员登录的设备信息
        //获取设备的IMEI
        if (isset($_POST['imei']) && !empty($_POST['imei'])) {

            //如果  imei 设备全部是零，表示手机没开启权限，必须要求用户强制开启
            if (intval($_POST['imei']) == 0) {
                return 3;
            }

            $data['imei'] = $_POST['imei'];
            //获取设备的名称
            if (isset($_POST['device_name']) && !empty($_POST['device_name'])) {
                $data['device_name'] = $_POST['device_name'];
                //获取业务员ID
                if (isset($_POST['clerk_id']) && !empty($_POST['clerk_id'])) {
                    $data['clerk_id'] = $_POST['clerk_id'];
                }
            }
        }
        //end

        $rsa = new lib\rsa();
        $encrypt_string = $rsa->encrypt(json_encode($data));
        $res_ = curl_post("https://api.mch.yixinu.com/verify/merchant_info", array('data' => $encrypt_string));

        if ($res_) {
            $res = json_decode($res_, 1);
            if (isset($res['login_status']) && $res['login_status'] == 1) {
                $GLOBALS['device_name'] = $res['device_name'];
                if($data['device_name']!=$res['device_name']){  //账号没有退出 ，且当前登录设备号不相同，则表示是同时在两台手机登录
                    return 2;   //换了手机登录，并且之前的账号没有退出
                }
            }
            if ($sign == md5($rand . $res['key'])) {
                return 1;
            }
        }
//                echo md5($rand . $res['key']); exit();
        return 0;
    }
}

function mchVerify() {
    if ($_POST && is_array($_POST) && count($_POST) > 0) {
        if (isset($_POST['mchid']) && !empty($_POST['mchid']) &&
                isset($_POST['rand']) && !empty($_POST['rand']) &&
                isset($_POST['sign']) && !empty($_POST['sign'])
        ) {
            $bool = mch_verify($_POST['mchid'], $_POST['rand'], $_POST['sign']);
            if ($bool == 1) {
                return true;
            } elseif ($bool == 2) {
                echo json_encode(array('errcode' => 1, 'errmsg' => '不允许同时登录两台设备，请先在[' . $GLOBALS['device_name'] . ']设备上退出登录或者通知管理员清空登录设备信息。'));
                exit();
            } elseif ($bool == 3) {
                echo json_encode(array('errcode' => 1, 'errmsg' => '无法读取手机设备号，请确认已开启权限！'));
                exit();
            } else {
                echo json_encode(array('errcode' => 1, 'errmsg' => '请检查商户号是否正确！'));
                exit();
            }
        } else {
            echo json_encode(array('errcode' => 1, 'errmsg' => '请检查参数是否齐全！'));
            exit();
        }
    } else {
        echo json_encode(array('errcode' => 1, 'errmsg' => '请检查数据是否为空！'));
        exit();
    }
}

/**
 * 一级菜单 和 二级菜单 匹配函数
 * @param string $url   要匹配的url参数
 * @return null
 */
function matchurl($url = null, $type = 1) {
    if ($type == 1) {
        if (empty($url)) {
            $querystring = $_SERVER['QUERY_STRING'];
        } else {
            $querystring = $url;
        }

        preg_match("/^([a-zA-Z]+=[a-z|A-Z|\-|\_]+\&?.*)/", $querystring, $matches);
        if (!empty($querystring)):
            return $matches[1];
        else:
            return null;
        endif;
    }

    elseif ($type == 2) {
        if (empty($url)) {
            $querystring = $_SERVER['QUERY_STRING'];
        } else {
            $querystring = $url;
        }

        preg_match("/^([a-zA-Z]+=[a-z|A-Z|\-|\_]+)\&?(.*)/", $querystring, $matches);
        if (!empty($querystring)):
            return $matches[1];
        else:
            return null;
        endif;
    }
}

/**
 * 解析频道字段，根据类型输出html代码
 * @param array $array  字段  表 fields 里面的数据 
 * @param array $extFieldValue 字段 对应 的存储 的值
 */
function parseChannelFieldType($array = null, $extFieldValue = null) {
    $extVal = null;
    if (is_array($array) && !empty($array)) {
        foreach ($array as $val) {
            $mark = $val['mark'];
            $html = null;
            switch ($val['type']) {
                case 'varchar':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'text':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<textarea name="' . $mark . '" class="textarea4" >' . $extVal . '</textarea> ';
                    break;
                case 'html':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<textarea name="' . $mark . '" class="textarea3" >' . $extVal . '</textarea> '
                            . '<script>KindEditor.ready(function(K){ editor_' . $mark . '=K.create(\'textarea[name="' . $mark . '"]\', { width: 750,height: 450 ,items : [ \'source\',\'fontname\', \'fontsize\', \'|\', \'forecolor\', \'hilitecolor\', \'bold\', \'italic\', \'underline\', \'removeformat\', \'|\', \'justifyleft\', \'justifycenter\', \'justifyright\', \'insertorderedlist\', \'insertunorderedlist\', \'|\', \'emoticon    s\', \'image\',\'|\',  \'myimage\', \'link\'] }); });</script>';
                    break;
                case 'int':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'float':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'datetime':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'thumb':
                    if (is_array($extFieldValue))
                        $extVal_ = $extFieldValue[$mark];
                    if (!empty($extVal_)) {
                        $extVal = '<div class="hoverArea"><img border="0" src="' . IMAGE_URL . $extVal_ . '"><a onclick="deleteImgSingle(this)" style="color: red;">删除图片</a><input type="hidden" name="' . $mark . '" value="' . $value . '"></div>';
                    }
                    $html = '<input class="bt1" onclick="insertImage({mode:\'single\',divid:\'thumb_' . $mark . '\',inputname:\'' . $mark . '\',\'level\':\'1\'})" type="button" value="添加图片" />';
                    $html .= '<div id=\'thumb_' . $mark . '\' class="ticlass">' . $extVal . '</div>';
                    break;
                case 'image':
                    if (is_array($extFieldValue)) {
                        $extVal_ = $extFieldValue[$mark];
                        if (is_array(unserialize($extVal_))) {
                            foreach (unserialize($extVal_) as $key => $value) {
                                if (!empty($value)) {
                                    $extVal .= '<li><div class="hoverArea"><img border="0" src="' . IMAGE_URL . $value . '"><a onclick="deleteImg(this)" style="color: red;">删除图片</a><input type="hidden" name="' . $mark . '[]" value="' . $value . '"></div></li>';
                                }
                            }
                        }
                    }
                    $html = '<div><input onclick="insertImage({\'divid\':\'imagelist_' . $mark . '\',\'inputname\':\'' . $mark . '[]\',\'level\':\'1\'})" type="button" class="bt1" value="添加图片" /></div>';
                    $html .= '<div class="area4 clearfix"><ul class="clearfix imglist" id=\'imagelist_' . $mark . '\'><div>' . $extVal . '</div></ul></div>';
                    break;
                case 'file':
                    break;
                case 'radio':
                    break;
                case 'checkbox':
                    $value_ = $val['val'];
                    $value__ = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $value_));
                    foreach ($value__ as $v2) {
                        list($i1, $i2) = explode('=', $v2);
                        $html .= '<input type="checkbox" value="' . $i1 . '"  />' . $i2;
                    }
                    break;
                    break;
                case 'select':
                    $value_ = $val['val'];
                    $value__ = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $value_));
                    $option_ = null;
                    foreach ($value__ as $v2) {
                        list($i1, $i2) = explode('=', $v2);
                        $option_ .= '<option value="' . $i1 . '" >' . $i2 . '</option>';
                    }
                    $html = '<select name="' . $mark . '" >' . $option_ . '</select>';
                    break;
                default:
                    break;
            }
            echo '<tr id="nohover" >'
            . '<td style="text-align: right;"><span class="fb">' . $val['name'] . '：</span></td>'
            . '<td>' . $html . '</td>'
            . '</tr>';
            $extVal = null;
        }
    } else {
        echo '';
    }
}

/**
 * 更新商品缓存
 */
function up_goods_cache() {
    $redis = new \models\yredis();
    $model = new \core\model();
    $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
            . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
            . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou"
            . " from " . $model->prefix . "goods as g "
            . "left join " . $model->prefix . "category as gc on g.catId=gc.id "
            . "left join " . $model->prefix . "goods_additional as ga on g.id=ga.goodsId "
            . " left join `" . $model->prefix . "activity_goods` ag on ga.id=ag.goodsid "
            . " left join " . $model->prefix . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
//                . "left join `$tablename` as a on ga.sn=a.sn ";
    $list = $model->select($sql);
    foreach ($list as $key => $value) {
        $redis->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));

        //更新库存和销量
        $redis->setex(REDIS_PRE . 'goods_salesval_' . $value['id'], 86400 * 10, (int) $value['salesval']);  //销量
        $redis->setex(REDIS_PRE . 'goods_numbers_' . $value['id'], 86400 * 10, (int) $value['numbers']);  // 库存 
    }

    //缓存每个仓库的商品库存
    // 缓存之前必须全部清空之前的，否则下单后减掉的库存没有清空，
    // 下次下单没有给出库存警告信息，库存不足的情况下仍然能下单，会导致库存错误
    $arr2 = $redis->keys(REDIS_PRE . 'stores_goods_*');
    foreach ($arr2 as $v) {
        $redis->del($v);
    }
    $sql = "SELECT og.goods_id, og.goods_name, sum(nums) as n , sum(delivery_quantity) as delivery_quantity , (
                    SUM( og.nums ) - SUM( og.delivery_quantity )
                    ) AS nums, o.warehouse_name, o.warehouse_id, g.numbers
                    FROM  `" . SQLPRE . "purchase_order_goods` og
                    INNER JOIN  `" . SQLPRE . "goods_additional` g on og.goods_id = g.id
                    INNER JOIN  `" . SQLPRE . "purchase_order` o on og.purchase_order_id = o.id
                    WHERE og.status =1 and og.delivery_quantity < og.nums
                    GROUP BY og.goods_id, o.warehouse_id";
    $stores_goods = $model->select($sql);
    foreach ($stores_goods as $key => $value) {
        $redis->setex(REDIS_PRE . 'stores_goods_' . $value['warehouse_id'] . '_' . $value['goods_id'], REDIS_TTL, $value['nums']);
    }

    //缓存仓库信息
    $sql = "select *,mark as yixinu from " . SQLPRE . "stores";
    $stores_list = $model->select($sql);
    foreach ($stores_list as $mark => $value) {
        $redis->setex(REDIS_PRE . 'stores_info_' . $mark, REDIS_TTL, json_encode($value));
    }


    //删除所有商品分类的缓存
    $arr = $redis->keys(REDIS_PRE . 'goodscate_info*');
    foreach ($arr as $v) {
        $redis->del($v);
    }

    //记录更新商品缓存的时间
    $redis->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());
}

/**
 * 获取商品编号
 * @param type $type  参数：create创建编号，change修改编号使用状态
 * @param type $len 参数$type为create时表示需要多少个编号，参数$type为change时表示十六进制的编号
 * @return string or arr 返回一个字符串或者数组，$len大于1时返回数组，等于1时返回一个字符串。
 */
function get_goods_sn($type = 'create', $len = 1) {
    $m = new core\model();
    if ($type == 'create') {
        $goodsSn = NULL;
        if ($len == 1) {  //如果只要一个编号
            $goodsSn_ = $m->find("select id from " . SQLPRE . "sn2 where `use`=0 order by id");
            if ($goodsSn_) {
                $goodsSn = dechex($goodsSn_['id']); //转成16进制
            } else {
                $goodsSn = $m->save(array('v' => 1), 'sn2');   //创建编号
                $goodsSn = dechex($goodsSn); //转成16进制
            }
            return $goodsSn;
        } elseif ($len > 1) {    //如果需要多个
            $goodsSn_ = $m->select("select id from " . SQLPRE . "sn2 where `use`=0 order by id");
            if (count($goodsSn_) < $len) { //如果不够数量就创建
                for ($i = 0; $i < $len; $i++) {
                    $m->save(array('v' => 1), 'sn2');   //创建编号
                }
            }
            $goodsSn_arr = $m->select("select id from " . SQLPRE . "sn2 where `use`=0 order by id limit $len");
            foreach ($goodsSn_arr as $key => $value) {
                $goodsSn[$key] = dechex($value['id']);
            }
            return $goodsSn;
        }
    } elseif ($type == 'change') {
        $id = hexdec($len);
        $res = $m->save(array('use' => 1), 'sn2', 'u', "id=$id"); //保存编号使用状态
        return $res;
    }
}

/**
 * 根据编号获取地址名称
 * @param type $proviceSn
 * @param type $citySn
 * @param type $countySn
 * @return type
 */
function get_addres_name($proviceSn, $citySn, $countySn) {
    $m = new \core\model();
    $data = array();
    $data['provice_name'] = $m->field("select provice_name  from " . $m->prefix . "area_provice  where provice_id ='$proviceSn' ");
    $data['city_name'] = $m->field("select city_name  from " . $m->prefix . "area_city  where city_id ='$citySn' ");
    $data['county_name'] = $m->field("select county_name  from " . $m->prefix . "area_county  where county_id ='$countySn' ");
    return $data;
}

/**
 * 扣库存 , 更新库存
 */
function buckle_stock($order_id,$m=null) {
    if (!$m)
        $m = new \core\model();
    $order_info = $m->find("select id,orderSn,userId,delivery_warehouse,warehouse_name from `" . SQLPRE . "orders` where id=$order_id");
    $d['warehouse_id'] = $order_info['delivery_warehouse'];
    $d['warehouse_name'] = $order_info['warehouse_name'];
    $d['userId'] = $order_info['userId'];
    $d['orderSn'] = $order_info['orderSn'];
    $d['reason'] = 2;   //2为下单出库
    $d['type'] = 2; //2为出库

    $goods_list = $m->select("select goodsid,goodsname,goodssn,goodsnum from " . SQLPRE . "order_goods where orderid=$order_id");
    foreach ($goods_list as $g) {
        if ($g['goodsnum'] < 0) {
            continue;
        }
        $goods['goods_id'] = $g['goodsid'];
        $goods['goods_name'] = $g['goodsname'];
        $goods['nums'] = $g['goodsnum'];
        buckle_stock_specify_goods($goods, $d);
    }

    //更新缓存
    up_goods_cache();
}

/**
 * 出库，指定一个商品. (接上面函数)
 * 相关表: _inventory_records , purchase_order_goods 
 * 
 * @param array $goods 传入的商品数组数据
 * @param string $goods['goods_id'] 商品ID
 * @param string $goods['goods_name'] 商品名称
 * @param string $goods['nums'] 出库数量
 * 
 * @param array $d 传入的仓库及其他数据
 * @param string $d['warehouse_id'] 仓库ID
 * @param string $d['warehouse_name'] 仓库名称
 * @param string $d['reason'] 出入库原因
 * @param string $d['type'] 出库或入库
 * 
 * @param string $d['userId'] 订单的用户ID  (可以不传)
 * @param string $d['orderSn'] 订单号   (可以不传)
 * 
 */
function buckle_stock_specify_goods($goods = array(), $d = array(), $m = null) {
    if (!$m)
        $m = new \core\model();
    $redis = new models\yredis();

    $d['type'] = 2; //出库
    $data = array_merge($goods, $d);
    //如果有订单号，就记录订单号
    if (!isset($data['comments'])) {
        $data['comments'] = isset($data['orderSn']) ? $data['orderSn'] : "";
    }
    $data['store_name'] = $m->field("select store_name from ".SQLPRE."users where id=$data[userId]");
    unset($data['userId']);
    unset($data['orderSn']);
    //写出库记录
    $insert_id = $m->save($data, 'inventory_records');


    //begin 从仓库减库存
    //取出每一批采购的商品，按批次减库存
    $sql = "select og.id,og.nums,og.delivery_quantity,( og.nums - og.delivery_quantity ) as total,o.warehouse_id "
            . " from `" . SQLPRE . "purchase_order_goods` og "
            . " inner join `" . SQLPRE . "purchase_order` o on og.purchase_order_id=o.id "
            . " where og.goods_id=$data[goods_id] and o.warehouse_id=$data[warehouse_id] and og.delivery_quantity<og.nums order by og.id desc ";
    $goods = $m->select($sql);
    if (!$goods): return false;
    endif;

    $nums_ = 0;  //用来保存实际减的库存
    foreach ($goods as $item) {
        if ($item['total'] <= 0) {
            continue;
        }
        $nums = $data['nums']; //保存之前要减的数量 ，以免计算后原来的值会改变
        //要出库的数量减去这一批次可出库数量 ，而不是减这一批次的库存数量
        $data['nums'] = intval($data['nums']) - intval($item['total']);

        /*
         * 写入到数据库的出库数量 ，如果大于0，表示减的数量比这一批可出库数量要大，
         * 下一批要接着减 ，所以出库数量就是这一批的可出库数量，否则就是填要减的数量
         */
        $write_num = $data['nums'] > 0 ? $item['total'] : $nums;
        $write_data['delivery_quantity'] = "`delivery_quantity`+$write_num---";

        $m->save($write_data, 'purchase_order_goods', 'u', "id=$item[id]");
        $nums_ = $nums_ + $write_num;  //把每一次减去的加起来就是总共要减的库存
        //如果减完了，直接退出
        if ($data['nums'] <= 0) {
            break;
        }
    }
    //end
    //begin 减总库存
    $d3['numbers'] = "`numbers`-$nums_---"; //减库存
    if (isset($d['orderSn']) && $d['orderSn']) {
        //只有传入了订单号才能算销量，如果是调拨或者报损是不能算销量的。
        $d3['salesval'] = "`salesval`+$nums_---"; //增加销量
    }
    $m->save($d3, 'goods_additional', 'u', "id=$data[goods_id]");
    //end
    //销量和库存同时记录到redis
    if (isset($d['orderSn']) && $d['orderSn']) {
        //只有传入了订单号才能算销量，如果是调拨或者报损是不能算销量的。
        $redis->incrBy(REDIS_PRE . 'goods_salesval_' . $data['goods_id'], $nums_);  //增加销量
    }
    $redis->decrBy(REDIS_PRE . 'goods_numbers_' . $data['goods_id'], $nums_);  //减库存

    /**
     * 如果传入了用户ID
     * 记录用户购买记录，用作限购的判断
     */
    if (isset($d['userId']) && $d['userId']) {
        ini_set('date.timezone', 'Asia/Shanghai');
        $n1 = $redis->get(REDIS_PRE . 'goods_xianglouinfo_' . $d['userId'] . '-' . $data['goods_id']) ?: 0;
        $redis->setex(REDIS_PRE . 'goods_xianglouinfo_' . $d['userId'] . '-' . $data['goods_id']
                , strtotime(date("Y-m-d") . " 23:59:59") - time()
                , $nums_ + $n1);
    }


    //更新出库记录,更新最后实际减的库存
    $m->save(array("nums" => $nums_), 'inventory_records', 'u', "id=$insert_id");
    //设置商品库存状态
    reset_purchase_order_goods_status($data['goods_id'], $m);
    return true;
}

/**
 * 修改 库存数量为0的商品 状态为0
 * @param type $goods_id
 */
function reset_purchase_order_goods_status($goods_id, $m = null) {
    if (!$m)
        $m = new \core\model();
    $m->save(array('status' => 0), 'purchase_order_goods', 'u', "`nums`-`delivery_quantity`=0 and status=1 and goods_id=$goods_id");
}

/**
 * 取仓库信息
 * @param type $mark
 */
function get_stores($mark) {
    $redis = new models\yredis();
    $info_ = $redis->get(REDIS_PRE . 'stores_info_' . $mark);
    $info = json_decode($info_, 1);
    if (is_array($info) && count($info) > 0) {
        return $info;
    } else {
        return false;
    }
}

/**
 * 退货入库，单个商品的操作
 * 
 * @param type $goodsid  商品ID
 * @param type $num 退货数量
 * @param type $warehouse  仓库id
 */
function sales_return($goodsid, $num = 1, $warehouse = 2) {
    $m = new \core\model();
    $redis = new models\yredis();
    //取出每一批采购的商品，按批次减库存
    $sql = "select og.id,og.nums,og.delivery_quantity,o.warehouse_id "
            . " from `" . SQLPRE . "purchase_order_goods` og "
            . " inner join `" . SQLPRE . "purchase_order` o on og.purchase_order_id=o.id "
            . " where og.goods_id=$goodsid and o.warehouse_id=$warehouse and og.delivery_quantity>0 order by og.delivery_quantity desc ";
//    var_dump($sql);
    $purchase_order_goods = $m->select($sql);
//    var_dump($purchase_order_goods);
}

/**
 * 模板中判断设置checked
 * @param type $v
 */
function layoutInputCheck($n = 1, $v = 1, $t = 1) {
    if ($t == 1) {
        if ($n == $v) {
            echo "checked='checked'";
        } else {
            echo "";
        }
    }

    if ($t == 2) {
        if ($n == $v) {
            echo "selected='selected'";
        } else {
            echo "";
        }
    }
}

/**
 * 数字金额转换成中文大写金额的函数
 * String Int $num 要转换的小写数字或小写字符串
 * return 大写字母
 * 小数位为两位
 * */
function num_to_rmb($num) {
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    //精确到分后面就不要了，所以只留两个小数位
    $num = round($num, 2);
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "金额太大，请检查";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int) $num;
        //结束循环
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    //将处理的汉字加上“整”
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}

function yinzhang($hos = null) {
    if (!isset($hos))
        exit;
    $im = ImageCreate(150, 150);
    $gray = ImageColorResolveAlpha($im, 200, 200, 200, 127);
    $red = ImageColorAllocate($im, 255, 88, 88);
    for ($i = 0; $i < 6; $i++)
        ImageArc($im, 75, 75, 148 - $i, 148 - $i, 0, 360, $red);
    $stock = IFRAME_ROOT . '/api/simkai.ttf';
    $point = "★";
    $size = 30;
    ImageTTFText($im, $size, 0, 72 - $size / 2, 72 + $size / 2, $red, $stock, $point);
    $a = 75;
    $b = -75; //中心点坐标
    $r = 65;
    $m = 40; //半径，角度
    $size = 16; //字体大小
    $r = $r - $size;
    $word = array();
    $max = 18;
    $count = mb_strlen($hos, 'utf8');
    if ($count > $max)
        $count = $max;
    if ($count > 12){
        $m = floor(340 / $count);
    }else if ($count > 5){
        $m -= $count;
    }
    for ($i = 0; $i < $count; $i++)
        $word[] = mb_substr($hos, $i, 1, 'utf8');
    $j = floor($count / 2);
    if ($j != $count / 2) {
        for ($i = $j; $i >= 0; $i--) {
            $arc = $m * ($j - $i) + $size / 2;
            $x = round($r * cos((90 + $arc) * M_PI / 180)) + $a;
            $y = -1 * (round($r * sin((90 + $arc) * M_PI / 180)) + $b);
            if ($arc < 10)
                $arc = 0;
            ImageTTFText($im, $size, $arc, $x, $y, $red, $stock, $word[$i]);
            $arc = $m * ($j - $i) - $size / 2;
            $x = round($r * cos((90 - $arc) * M_PI / 180)) + $a;
            $y = -1 * (round($r * sin((90 - $arc) * M_PI / 180)) + $b);
            if ($arc < 10)
                $arc = 0;
            ImageTTFText($im, $size, -$arc, $x, $y, $red, $stock, $word[$j + $j - $i]);
        }
    }
    else {
        $j = $j - 1;
        for ($i = $j; $i >= 0; $i--) {
            $arc = $m / 2 + $m * ($j - $i) + $size / 2;
            $x = round($r * cos((90 + $arc) * M_PI / 180)) + $a;
            $y = -1 * (round($r * sin((90 + $arc) * M_PI / 180)) + $b);
            ImageTTFText($im, $size, $arc, $x, $y, $red, $stock, $word[$i]);
            $arc = $m / 2 + $m * ($j - $i) - $size / 2;
            $x = round($r * cos((90 - $arc) * M_PI / 180)) + $a;
            $y = -1 * (round($r * sin((90 - $arc) * M_PI / 180)) + $b);
            ImageTTFText($im, $size, -$arc, $x, $y, $red, $stock, $word[$j + $j + 1 - $i]);
        }
    }
    header('Content-Type:image/png');
    ImagePNG($im);
}



/**
 * 向微信用户发送消息
 * @param type $openid
 * @param type $content
 * @return type
 */
function push_msg($openid, $content) {
    $config = get_weixin_config();
    require_once IFRAME_ROOT . '/api/jssdk.php';
    $jssdk = new JSSDK($config['appid'], $config['secret']);
    $access_token = $jssdk->getAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
    $post_arr = array(
        'touser' => $openid,
        'msgtype' => 'text',
        'text' => array(
        'content' => $content,
        )
    );
    $post_str = json_encode($post_arr, JSON_UNESCAPED_UNICODE);
    $return = httpRequest($url, 'POST', $post_str);
    $return = json_decode($return, true);
    return $return;
}


/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
	//return array($http_code, $response,$requestinfo);
}


/**
 * 导出excel文件 
 * 
 * @param type $file_name
 * @param type $title
 * @param type $data
 * @param type $title2
 * @param type $data2
 */
function export_excel($file_name, $title, $data, $title2 = '', $data2 = '') {

// 输出Excel文件头  
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename = {$file_name}.csv");
    header('Cache-Control: max-age=0');

// 打开PHP文件句柄，php://output 表示直接输出到浏览器  
    $fp = fopen('php://output', 'a');

// 输出Excel列名信息  
    foreach ($title as $i => $one) {
        $head[$i] = iconv('utf-8', 'gbk', $one);
    }
// 将数据通过fputcsv写到文件句柄  
    fputcsv($fp, $head);

// 输出Excel内容  
    foreach ($data as $one) {
        $row = array();
        foreach ($one as $j => $v) {
            $row[$j] = iconv('utf-8', 'gbk', $v);
        }
        fputcsv($fp, $row);
    }
//空格换行  
    fputcsv($fp, array(''));
    fputcsv($fp, array(''));
    fputcsv($fp, array(''));

//另一块数据  
// 输出Excel列名信息  
    foreach ($title2 as $i => $one) {
        $head[$i] = iconv('utf-8', 'gbk', $one);
    }
// 将数据通过fputcsv写到文件句柄  
    fputcsv($fp, $head);

// 输出Excel内容  
    foreach ($data2 as $one) {
        $row = array();
        foreach ($one as $j => $v) {
            $row[$j] = iconv('utf-8', 'gbk', $v);
        }
        fputcsv($fp, $row);
    }
}

/**
 * html中，显示图片，返回图片地址
 * @param type $src
 * @param type $type  t表示缩略图，s表示源图
 */
function showimg($src="",$type="t"){
    if($src){
        if($type=="t"){
            //如果源地址本来就是缩略图则不需要替换
            if(strpos($src,'thumb_')){
                echo UPLOAD_URL.$src;
            }else{
                //插入字符：thumb_，拼接缩略图文件名
                $s=preg_replace("/^(.*)\/([0-9]{1,}).([a-z]{3,4})$/i","$1/thumb_$2.$3",$src);
                echo UPLOAD_URL.$s;
            }
        }elseif($type=='s'){   //如果是源图
            if(strpos($src,'thumb_')){
                $s = str_replace('thumb_', '', $src);
                echo UPLOAD_URL.$s;
            }else{
                echo UPLOAD_URL.$src;
            }
        }
    }
}
function showimg_return($src="",$type="t"){
    if($src){
        if($type=="t"){
            //如果源地址本来就是缩略图则不需要替换
            if(strpos($src,'thumb_')){
                return UPLOAD_URL.$src;
            }else{
                //插入字符：thumb_，拼接缩略图文件名
                $s=preg_replace("/^(.*)\/([0-9]{1,}).([a-z]{3,4})$/i","$1/thumb_$2.$3",$src);
                return UPLOAD_URL.$s;
            }
        }elseif($type=='s'){   //如果是源图
            if(strpos($src,'thumb_')){
                $s = str_replace('thumb_', '', $src);
                return UPLOAD_URL.$s;
            }else{
                return UPLOAD_URL.$src;
            }
        }
    }
}

/**
 * 写API访问日志
 */
function writeapilog(){
    $m = new core\model();
    $data['get'] = serialize($_GET);
    $_POST = array_merge($_GET, $_POST);
    $data['var'] = serialize($_POST);
    $data['url'] =  'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    $data['ip'] = $_SERVER['REMOTE_ADDR'];
    $m->save($data, 'apilog');
}


// ###################### 模板函数  start  ###################### 
// 
//返回template对象
function obj1() {
    return new \models\template();
}

//获取一个栏目数据
function getcolumn($id, $type = 'pc') {
    return obj1()->getcolumn($id, $type);
}

//获取一个商品分类
function getgoodscate($id = 0) {
    return obj1()->getgoodscate($id);
}

//获取一个栏目的文章列表
function getartlist($id = 0, $num = 10, $order = 1) {
    return obj1()->getartlist1($id, $num, $order);
}

//获取一个频道的文章列表
function getartlist2($id = 0, $num = 10, $order = 1) {
    return obj1()->getartlist2($id, null, $num, $order);
}

//获取自定义数据组
function getcustom1($id) {
    return obj1()->getcustom1($id);
}

//获取招聘职位列表
function getjobslist($num = 10) {
    return obj1()->getjobslist($num);
}

//获取一个商品分类下的所有商品数据
function getonegoodscate($id = null, $num = 10) {
    return obj1()->fieldgoodscate($id, $num);
}

/**
 * 获取一个滚动图片组的图片数据
 * @param type $id
 * @return array  {id,rid,link,img} {id,rid,链接,图片地址}
 */
function getrollimage($id) {
    return obj1()->getrollimage($id);
}

function getgoodssoncate($id, $limit = 10) {
    return obj1()->getgoodssoncate($id, $limit);
}

// ###################### 模板函数  end  ###################### 




