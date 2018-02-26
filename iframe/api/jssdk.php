<?php

class JSSDK {

    public $appId;
    public $appSecret;

    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function getJsApiTicket() {
        $redis = new \models\yredis();
        if ($redis->get(REDIS_PRE . 'wx_jsapi_ticket')) {
            $res = $redis->get(REDIS_PRE . 'wx_jsapi_ticket');
            $data = json_decode($res,1);
            $ticket = $data['jsapi_ticket'];
        }
        if ( !isset($data['expire_time']) || $data['expire_time'] < time() ) {
            $accessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode(@file_get_contents($url));
//            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                $redis->setex(REDIS_PRE . 'wx_jsapi_ticket', 7000, json_encode($data));
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }
        return $ticket;
    }

    public function getAccessToken() {
        $redis = new \models\yredis();
        if ($redis->get(REDIS_PRE . 'wx_access_token')) {
            $res = $redis->get(REDIS_PRE . 'wx_access_token');
            $data = json_decode($res,1);
            $access_token = $data['access_token'];
        }
        if (  !isset($data['expire_time']) || $data['expire_time'] < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode(@file_get_contents($url));
//            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $d['expire_time'] = time() + 7000;
                $d['access_token'] = $access_token;
                $redis->setex(REDIS_PRE . 'wx_access_token', 7000, json_encode($d));
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }

    public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        @curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        @curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

}
