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




