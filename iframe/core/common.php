<?php

/**
 * 系统基础函数库
 */

/**
 * 读取框架语言,如果$name为空则返回整个数组，默认读取中文
 */
function Lsys($name = null) {
    $lang = include ICONF . 'lang.php';
    if (empty($name)) {
        return $lang[LANG];
    } else {
        return $lang[LANG][$name];
    }
}

/**
 * 读取配置
 * @param type $name
 * @return type
 */
function cfg($name = null) {
    $_cfg = array();
    $core_cfg = include ICONF . 'config.php';
    $_cfg = array_merge($_cfg, $core_cfg);
	return $_cfg[$name];
}

/**
 * 打印 消息 
 * 
 * @param type $content     内容 
 * @param type $url     跳 转的链接  
 * @param type $urltitle    链接 标题
 * @param type $waitSecond    多少秒钟 之后自动 跳 转
 */
function msg($content = '', $url = __ROOT__, $urltitle = '[默认链接]', $waitSecond = 300) {
    $title = 'error';
    //检测项目中是否定义了  message函数
    if (function_exists('message')) {
        message($content);
    }

    if (judgeMobileBrowse()) {
        include IVIEW . 'msg_1.php';
    } else {
        include IVIEW . 'msg.php';
    }
    exit();
}

/**
 * 模拟IE浏览器 抓取网页
 * @param type $from_url   表示这个访问 是从 $form_url 这个链接点过去的。
 * @param type $url   要获取的网页 url
 * @return type 
 */
function gethtml($from_url, $url, $data = array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_REFERER, $from_url); //设置来路，这个很重要，表示这个访问 是从 $form_url 这个链接点过去的。
    curl_setopt($ch, CURLOPT_URL, $url); //获取 的url地址 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //设置返回原生的（Raw）输出
    //如果有数据提交
    if ($data && count($data) > 0) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    //模拟浏览器发送报文 ，这里模拟 IE6 浏览器访问 
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * 远程获取数据，POST模式
 * 
 * @param type $url  指定URL完整路径地址
 * @param type $param  请求的数据
 */
function curl_post($url, $data = null) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * 远程获取数据，GET模式
 * 
 * @param type $url  指定URL完整路径地址
 * @param type $param  请求的数据
 */
function curl_get($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * 保存和读取缓存数据，自动判断 是保存还是读取
 * @param type $arr   数组;
 * @param type $key  键值 也是文件名
 * @param type $time  缓存时间，是在读取的时候 设置 的 
 * @return array  如果 是读取缓存  ，则返回 的是数组;
 */
function s($key, $arr = null, $time = 200) {
    $filename = md5($key) . '.php';
    $write = false;   //是否要写缓存 
    //检查缓存是否存在
    if (file_exists(CACHE_PATH . $filename)) {
        $time_ = $time + filemtime(CACHE_PATH . $filename);
        //如果缓存已经过期
        if ($time_ < time()) {
            //如果写缓存数据存在
            if (is_array($arr)) {
                $write = true;
            } else {  //如果缓存已经过期，并且写缓存数据也没有。
                return false;
            }
        } else {
            if (is_array($arr))
                $write = true;
        }
    }else {
        //如果写缓存数据存在
        if (is_array($arr)) {
            $write = true;
        } else {  //如果缓存文件不存在，并且写缓存数据也没有。
            return false;
        }
    }

    //如果需要写缓存 ，否则读取
    if ($write) {
        file_put_contents(CACHE_PATH . $filename, serialize($arr));
        return $arr;
    } else {
        return unserialize(file_get_contents(CACHE_PATH . $filename));
    }
}

/**
 * 判断是否用手机或电脑浏览
 * @return boolean 如果是用手机或微信浏览则返回true，如果用电脑浏览则返回false
 */
function judgeMobileBrowse() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') || strpos($user_agent, 'Android') || strpos($user_agent, 'Android') || strpos($user_agent, 'iPhone')) {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否在微信环境下
 * 
 * @return boolean  如果 是 返回 true
 */
function judgeMicroMessenger() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger')) {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是不是用ajax的方式请求，如果是返回 true;
 * @return boolean
 */
function judgeAjaxRequest() {
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
        return true;
    } else {
        return false;
    }
}

/**
 * 上传图片 
 * @param type $file   表单的files ，填写：$_FILES['upfile']
 * @param type $path   图片存放的路径
 * @param type $newfilename 新生成图片的文件名
 * @return array 返回新的文件名和图片的在服务器上的全路径、图片扩展名 ,  $data['name'] 和 $data['path'] 、$data['type']。
 */
function uploadImage($file = null, $path = './image', $newfilename = null) {
    if (!empty($file) and is_uploaded_file($file['tmp_name'])) {
        $photo_types = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/x-png'); //定义上传格式
        $max_size = 1024000;    //上传照片大小限制,默认1M
//        $name = $file['name'];
//        $type = $file['type'];
//        $size = $file['size'];
        $photo_name = $file["tmp_name"];
//            $photo_size = getimagesize($photo_name);
        //检查文件大小
        if ($max_size < $file["size"]) {
            return 401;       //echo "<script>alert('对不起，文件超过规定大小!');history.go(-1);</script>";
        }
        //检查文件类型
        if (!in_array($file["type"], $photo_types)) {
            return 402;       //echo "<script>alert('对不起，文件类型不符!');history.go(-1);</script>";
        }
        //服务器存放图片的路径
        $photo_folder = $path . DIRECTORY_SEPARATOR;
        //////  开始处理上传
        if (!file_exists($photo_folder)) {  //检查照片目录是否存在
            mkdir($photo_folder, 0770, true);  //mkdir("temp/sub, 0777, true);
        }
        $pinfo = pathinfo($file["name"]);
        $photo_type = $pinfo['extension']; //上传文件扩展名
        $time = time();
        $newFilename = $time . "." . $photo_type;  //图片文件名
        //如果有新的文件名
        if (!empty($newfilename)):
            $newFilename = $newfilename . "." . $photo_type;  //图片文件名
        endif;
        $imagePath = $photo_folder . $newFilename; //原图文件名，这里是加了路径的 
        //移动文件
        if (!move_uploaded_file($photo_name, $imagePath)) {
            return 403; //echo "移动文件出错";
        }
        //如果出错了，则返回错误
        $data['name'] = $newFilename;
        $data['path'] = $imagePath;
        $data['type'] = $photo_type;
        return $data;
    } else {
        return false;
    }
}

/**
 * 生成缩略图
 * @param type $filename  原文件 ，这里是全路径 
 * @param type $dst  目标文件 ，这里是全路径 
 * @param type $width  生成 缩略 图的宽度
 * @param type $height  生成 缩略 图的高度
 * @return boolean
 */
function makeThumb($filename, $dst, $width = 250, $height = 200) {
    $thumb_width = $width;
    $thumb_height = $height;

    //取得图片信息
    list($width_orig, $height_orig, $mime_type) = getimagesize($filename);
    //如果图片尺寸小于生成的尺寸则使用图片原尺寸缩放
    if ($width_orig < $width && $height_orig < $height) {
        $width = $width_orig;
        $height = $height_orig;
    }
    //算出缩放比例
    if ($width && ($width_orig < $height_orig)) {
        $width = ($height / $height_orig) * $width_orig;
    } else {
        $height = ($width / $width_orig) * $height_orig;
    }
    //发现生成的缩略图留了太多的白色不好看，所以这里重新生成宽度
    if($width<$thumb_width)
        $width = $thumb_width;

    //创建一张图片
    $image_p = imagecreatetruecolor($thumb_width, $thumb_height);
    //用白色填充背景
    $clr = imagecolorallocate($image_p, 180, 180, 180);
    imagefilledrectangle($image_p, 0, 0, $thumb_width, $thumb_height, $clr);
    switch ($mime_type) {
        case 1:
        case 'image/gif':
            $image = imagecreatefromgif($filename);
            break;

        case 2:
        case 'image/pjpeg':
        case 'image/jpeg':
            $image = imagecreatefromjpeg($filename);
            break;

        case 3:
        case 'image/x-png':
        case 'image/png':
            $image = imagecreatefrompng($filename);
            break;

        default:
            return false;
    }
    //算出复制图片居中的坐标
    $dst_x = ($thumb_width - $width) / 2;
    $dst_y = ($thumb_height - $height) / 2;
    //完成复制
    imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $width_orig, $height_orig);

    // 输出图片
    switch ($mime_type) {
        case 1:
        case 'image/gif':
            imagegif($image_p, $dst);
            break;

        case 2:
        case 'image/pjpeg':
        case 'image/jpeg':
            imagejpeg($image_p, $dst, 85);
            break;

        case 3:
        case 'image/x-png':
        case 'image/png':
            imagepng($image_p, $dst);
            break;
        default:
            return false;
    }
}

/*
 * 功能：php多种方式完美实现下载远程图片保存到本地
 * 参数：文件url,保存文件名称，使用的下载方式
 * 当保存文件名称为空时则使用远程文件原来的名称
 */

function getImage($url, $filename = '', $type = 1) {
    if ($url == '') {
        return false;
    }
    //文件保存路径 
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $img = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $img = ob_get_contents();
        ob_end_clean();
    }
    $size = strlen($img);
    //文件大小 
    $fp2 = @fopen($filename, 'a');
    fwrite($fp2, $img);
    fclose($fp2);
    return $filename;
}

/**
 * 递归处理数据
 * @param type $id
 * @param type $data
 * @return type
 */
function RecursionData($id = 0, $data) {
    $arr = null;
    foreach ($data as $key => $value) {
        if ($value['parentId'] == $id) {
            $arr[$value['id']] = $value;
            $arr[$value['id']]['son'] = RecursionData($value['id'], $data);
        }
    }
    return $arr;
}

/**
 * 生成 链接 。
 * 
 * @param type $action 控制器/方法名,中间用 / 隔开 ，如果没有 / 符号，则默认取当前 控制 器 加 传入的参数 作方法 
 * @param type $vars  传入 要生成 的GET变量 
 * @param type $hideindex  是否要隐藏 index.php 文件 名，使用伪静态
 */
function createLink($controllerAction = null, $vars = array(), $hideindex = false) {
    $controller = null;
    $action = null;
    $url = null;
    if (strstr($controllerAction, '/') != false) {
        list($controller, $action) = explode('/', $controllerAction);
    } else {
        $controller = CONTROLLER_NAME;
        $action = $controllerAction;
    }

    $GET = NULL;
    if (is_array($vars) and count($vars) > 0) {
        $GET = '/';
        foreach ($vars as $key => $value) {
            if ($value)
                $GET .= "$key" . '_' . "$value" . "_";
        }
        $GET = substr($GET, 0, -1);
    }
    //如果隐藏  index.php  文件 名
    if (!$hideindex) {
        $GET = substr($GET, 1);
        $url = __ROOT__ . 'index.php?v=/' . "$controller/$action/" . $GET;
    } else {
        $url = __ROOT__ . "$controller/$action" . $GET . '.html';
    }
    return $url;
}

//开启伪静态下的get
function rget($var = null) {
    return \core\url::_get($var);
}

/**
 * 随机返回一串指定长度的 小写字母组成的字符串
 * 
 * @param type $len
 * @return type
 */
function getabc($len = 0) {
    $str = '';
    $length = 10;
    if ($len == 0) {
        $length = rand(5, 25);
    } else {
        $length = $len;
    }
    for ($i = 0; $i < $length; $i++) {
        $h = rand(97, 122);
        $str .= chr($h);
    }
    return $str;
}

/* *
 * 对变量进行 JSON 编码
 * @param mixed value 待编码的 value ，除了resource 类型之外，可以为任何数据类型，该函数只能接受 UTF-8 编码的数据
 * @return string 返回 value 值的 JSON 形式
 */

function json_encode_ex($value) {
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        $str = json_encode($value);
        $str = preg_replace_callback(
                "#\\\u([0-9a-f]{4})#i", function( $matchs) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
        }, $str
        );
        return $str;
    } else {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 获取 HTTP 状态 ，不要写https
 * @param type $url
 * @return type
 */
function GetHttpStatusCode($url) {
    $arr = get_headers($url, 1);
    list($a, $b, $c) = explode(" ", $arr[0]);
    return $b;
}

/**
 * aes 加密
 * @param type $string
 * @param type $key
 * @return type
 */
function aes_encrypt($string, $key) {
    $key = md5($key);
    $CIPHER = MCRYPT_RIJNDAEL_128;
    $MODE = MCRYPT_MODE_ECB;
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($CIPHER, $MODE), MCRYPT_RAND);
    $d = mcrypt_encrypt($CIPHER, $key, $string, $MODE, $iv);
    return base64_encode($d);
}

/**
 * aes 解密
 * @param type $string
 * @param type $key
 * @return type
 */
function aes_decrypt($string, $key) {
    $key = md5($key);
    $CIPHER = MCRYPT_RIJNDAEL_128;
    $MODE = MCRYPT_MODE_ECB;
    $iv = mcrypt_create_iv(mcrypt_get_iv_size($CIPHER, $MODE), MCRYPT_RAND);
    $string = base64_decode($string);
    $d = mcrypt_decrypt($CIPHER, $key, $string, $MODE, $iv);
    $d = str_replace("\0", "", $d);  //偶尔发现传输过来的字符串尾部有很多\0的字符，造成JSON无法解析，所以这里替换掉
    return str_replace("'", '"', $d);  //因为 C++ 双引号问题 ，传过来的JSON字符串把双引号替换成了单引号，现在必须把单引号替换回来，才是一个有效的JSON格式数据
//        return $d;
}

/**
 * 求两个已知经纬度之间的距离,单位为千米
 * @param lng1,lng2 经度
 * @param lat1,lat2 纬度
 * @return float 距离，单位千米
 * */
function distance($lng1, $lat1, $lng2, $lat2) {//根据经纬度计算距离
    //将角度转为弧度
    $radLat1 = deg2rad($lat1);
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    $a = $radLat1 - $radLat2; //两纬度之差,纬度<90
    $b = $radLng1 - $radLng2; //两经度之差纬度<180
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
    return $s;
}

//人性化时间显示  
function formatTime($time) {
    $rtime = date("m-d H:i", $time);
    $htime = date("H:i", $time);
    $time = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时前 ';
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1) {
            $str = '昨天 ' . $rtime;
        } else {
            $str = '前天 ' . $rtime;
        }
    } else {
        $str = $rtime;
    }
    return $str;
}

/**
 * 获取单个汉字拼音首字母。注意:此处不要纠结。汉字拼音是没有以U和V开头的
 * @param type $s0
 * @return string
 */
function getfirstchar($s0) {   //
    $fchar = ord($s0{0});
    if ($fchar >= ord("A") and $fchar <= ord("z"))
        return strtoupper($s0{0});
    $s1 = @iconv("UTF-8", "gb2312", $s0);
    $s2 = @iconv("gb2312", "UTF-8", $s1);
    if ($s2 == $s0) {
        $s = $s1;
    } else {
        $s = $s0;
    }
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 and $asc <= -20284)
        return "A";
    if ($asc >= -20283 and $asc <= -19776)
        return "B";
    if ($asc >= -19775 and $asc <= -19219)
        return "C";
    if ($asc >= -19218 and $asc <= -18711)
        return "D";
    if ($asc >= -18710 and $asc <= -18527)
        return "E";
    if ($asc >= -18526 and $asc <= -18240)
        return "F";
    if ($asc >= -18239 and $asc <= -17923)
        return "G";
    if ($asc >= -17922 and $asc <= -17418)
        return "H";
    if ($asc >= -17922 and $asc <= -17418)
        return "I";
    if ($asc >= -17417 and $asc <= -16475)
        return "J";
    if ($asc >= -16474 and $asc <= -16213)
        return "K";
    if ($asc >= -16212 and $asc <= -15641)
        return "L";
    if ($asc >= -15640 and $asc <= -15166)
        return "M";
    if ($asc >= -15165 and $asc <= -14923)
        return "N";
    if ($asc >= -14922 and $asc <= -14915)
        return "O";
    if ($asc >= -14914 and $asc <= -14631)
        return "P";
    if ($asc >= -14630 and $asc <= -14150)
        return "Q";
    if ($asc >= -14149 and $asc <= -14091)
        return "R";
    if ($asc >= -14090 and $asc <= -13319)
        return "S";
    if ($asc >= -13318 and $asc <= -12839)
        return "T";
    if ($asc >= -12838 and $asc <= -12557)
        return "W";
    if ($asc >= -12556 and $asc <= -11848)
        return "X";
    if ($asc >= -11847 and $asc <= -11056)
        return "Y";
    if ($asc >= -11055 and $asc <= -10247)
        return "Z";
    return NULL;
    //return $s0;
}
/**
 * 获取整条字符串汉字拼音首字母
 * @param type $zh
 * @return type
 */
function pinyin_long($zh) {  //
    $ret = "";
    $s1 = @iconv("UTF-8", "gb2312", $zh);
    $s2 = @iconv("gb2312", "UTF-8", $s1);
    if ($s2 == $zh) {
        $zh = $s1;
    }
    for ($i = 0; $i < strlen($zh); $i++) {
        $s1 = substr($zh, $i, 1);
        $p = ord($s1);
        if ($p > 160) {
            $s2 = substr($zh, $i++, 2);
            $ret .= getfirstchar($s2);
        } else {
            $ret .= $s1;
        }
    }
    return $ret;
}
