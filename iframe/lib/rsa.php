<?php

/**
 *    因为加密有长度限制 ，如果要更为安全的加密方式，可以用支付宝的方式 ，将base64编码后的字符按５３位为一段加密，
 *    将每一段加密后的密文拼接回返，解码时再按64个字符为一段取出解密，解密的字符即为base64编码字符，拼接之后
 *    用base64解码即为解密后的内容
 *
 *    @author  青竹丹枫  email : 316686606@qq.com
 */

namespace lib;

/**
 *
 * @author kyle
 */
class rsa {

    public $pubkey;
    public $privkey;

    function __construct() {
        $this->pubkey = file_get_contents(ICONF . 'rsa_public_key.pem');
        $this->privkey = file_get_contents(ICONF . 'rsa_private_key.pem');
    }

    /**
     * 加密
     * @param string $data
     * @return boolean
     */
    public function encrypt($data) {
        //获取公钥
        $res = openssl_get_publickey($this->pubkey);
        if (!$res) {
            return false;
        }
        /**
         * 字符串按这个长度分割加密
         * 为什么要写５３，RSA加密的　OPENSSL_PKCS1_PADDING　模式数据长度为：
         *    将数据长度分成密钥长度-11byte，比如密钥是512bit，那么长度就是512/8-11=53bytes
         */
        $split_length = 53;

        //开始加密
        $content_ = base64_encode($data);
        //var_dump($content_);
        $num = intval(strlen($content_) / $split_length) + intval(strlen($content_) % $split_length);
        $string = '';
        //var_dump($num);
        for ($i = 0; $i < $num; $i++) {
            openssl_public_encrypt(substr($content_, $i * $split_length, $split_length), $encryptData1, $res);
            $string .= $encryptData1;
            //var_dump(strlen($encryptData1));
        }
        return $string;
    }

    /**
     * 解密
     * @param string $data
     * @return boolean
     */
    public function decrypt($data) {
        //获取私钥
        $res = openssl_get_privatekey($this->privkey);
        if (!$res) {
            return false;
        }

        /**
         * 为什么上面是53，这里是64，因为密钥长度是512，每一块数据加密后的密文长度就是64
         */
        $Ciphertext_length = 64;
        //开始解密
        $num = intval(strlen($data) / $Ciphertext_length) + intval(strlen($data) % $Ciphertext_length); //算出有多少块
        $string = '';
        //把每一块分开解密，把解密后的字符拼接
        for ($i = 0; $i < $num; $i++) {
            openssl_private_decrypt(substr($data, $i * $Ciphertext_length, $Ciphertext_length), $decryptData1, $res);
            $string .= $decryptData1;
        }
        //解密后的字符是base64编码，解码后的数据才是加密的原始数据
        return base64_decode($string);
    }

}
