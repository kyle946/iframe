<?php

/*
 * 微信公众号  相关函数
 */


/**
 * post  json数据到指定服务器
 * @param type $url
 * @param type $data   数组
 * @return type
 */
function curl_post_json($url, $data) {
    $data_string = json_encode($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
    );
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function https_post($url, $data = null) {
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
 /*
function curl_get($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
* */


###############################################################################################


/**
 * 验证签名
 * @return boolean
 */
function checkSignature() {
    if (isset($_GET["signature"]) && isset($_GET["timestamp"]) && isset($_GET["nonce"])) {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array(WEIXINTOKENCODE, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


/**
 * 获取token
 * @return array $token['access_token']
 */
function gettoken() {
    $token_url = 'https://api.weixin.qq.com/cgi-bin/token?appid=' . APPID . '&secret=' . SECRET . '&grant_type=client_credential';
    $token = json_decode(curl_get($token_url), 1);
    return $token;
}

/**
 * 创建菜单
 *
 * @param type $data
 */
function createMenu($data = array()) {
    if (count($data) <= 0 || !is_array($data)) {
        return false;
    }
    $token = gettoken();
    $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $token['access_token'];
    $return = https_post($url, json_encode_ex($data));
    return $return;
}


/**
 * 创建公众号二维码
 * @param type $expire  该二维码有效时间
 * @param type $type    二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
 * @param type $scene_id    场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
 *
 * @return string  返回二维码的链接
 */
function createQrcode($expire = 3600, $scene_id = 0, $type = 'QR_SCENE', $shorturl = 0) {
    $token = gettoken();
    $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token['access_token'];
    //创建二维码ticket
    $data = array(
        'expire_seconds' => $expire,
        'action_name' => $type,
        'action_info' => array(
            'scene' => array(
                'scene_id' => intval($scene_id)
            )
        )
    );
    $return = https_post($url, json_encode($data, JSON_UNESCAPED_UNICODE));
    $res = json_decode($return, 1);
//    file_put_contents('/tmp/1', print_r($res, 1));
    if ($res) {
        $str = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($res['ticket']);
        if ($shorturl) {
            $su = createshorturl($res['url']);
            $arr = array(
                'url' => $str,
                'shorturl' => $su,
            );
            return $arr;
        } else {
            return $str;
        }
    }
}

/**
 * 生成短链接
 *
 * @param type $url
 */
function createshorturl($url = '') {
    $token = gettoken();
    $data = array(
        'action' => 'long2short',
        'long_url' => $url,
    );
    $url = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=' . $token['access_token'];
    $return = https_post($url, json_encode($data, JSON_UNESCAPED_UNICODE));
    $res = json_decode($return, 1);
    if ($res['short_url']) {
        return $res['short_url'];
    } else {
//        file_put_contents('/tmp/error1', print_r($res,1));
        return 0;
    }
}

/**
 * 回复文本消息
 *
 * @param type $content
 */
function sendmsg($content = '', $to = null, $from = null) {
    //回复文本信息
    $textTpl = "<xml>
                                    <ToUserName><![CDATA[%s]]></ToUserName>
                                    <FromUserName><![CDATA[%s]]></FromUserName>
                                    <CreateTime>%s</CreateTime>
                                    <MsgType><![CDATA[%s]]></MsgType>
                                    <Content><![CDATA[%s]]></Content>
                                    <FuncFlag>0</FuncFlag>
                                    </xml>";
    $time = time();
    $resultStr = sprintf($textTpl, $to, $from, $time, 'text', $content);
    echo $resultStr;
}

################### app start

/**
 * 获取微信用户信息
 *
 * @param type $openid
 * @return boolean
 */
function wxuserinfo($openid = 0) {
    if (!$openid) {
        return false;
    }
    $token = gettoken();
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $token['access_token'] . '&openid=' . $openid . '&lang=zh_CN';
    $res = json_decode(curl_get($url), 1);
    return $res;
}

