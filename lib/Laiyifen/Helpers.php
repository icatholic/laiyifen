<?php
namespace Laiyifen;

/**
 * Defines a few helper methods.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class Helpers
{

    /**
     * 检测一个字符串否为Json字符串
     *
     * @param string $string            
     * @return true/false
     *
     */
    public static function isJson($string)
    {
        if (strpos($string, "{") !== false) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        } else {
            return false;
        }
    }

    /**
     * 除去数组中的空值和签名参数
     *
     * @param $para 签名参数组
     *            return 去掉空值与签名参数后的新签名参数组
     */
    public static function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if (strtolower(trim($key)) === "sign" || trim($val) === "")
                continue;
            else
                $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     *
     * @param $para 排序前的数组
     *            return 排序后的数组
     */
    public static function argSort($para)
    {
        ksort($para, SORT_STRING);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param $para 需要拼接的数组
     *            return 拼接完成以后的字符串
     */
    public static function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, strlen($arg) - 1);
        return $arg;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     *
     * @param $para 需要拼接的数组
     *            return 拼接完成以后的字符串
     */
    public static function createLinkstringUrlencode($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . rawurlencode($val) . "&";
        }
        // 去掉最后一个&字符
        $arg = substr($arg, 0, strlen($arg) - 1);
        
        return $arg;
    }

    /**
     * 获取随机字符串
     *
     * @param number $length            
     * @return string
     */
    public static function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 作用：array转xml
     */
    public static function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 作用：将xml转为array
     */
    public static function xmlToArray($xml)
    {
        $object = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return @json_decode(preg_replace('/{}/', '""', @json_encode($object)), 1);
    }
    
    /**
     * 
     * @param unknown $private_key_path
     * @return unknown
     */
    public function getopensslkey($key_path){
        $Key = file_get_contents($key_path);
        return $Key;
    }
    
    
    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
    public function rsaSign($data, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
    
    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 来伊份的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    public function rsaVerify($data, $public_key_path, $sign)  {
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }
    
    /**
     * RSA解密
     * @param $content 需要解密的内容，密文
     * @param $private_key_path 商户私钥文件路径
     * return 解密后内容，明文
     */
    public function rsaDecrypt($content, $private_key_path) {
        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        //用base64将内容还原成二进制
        $content = base64_decode($content);
        //把需要解密的内容，按128位拆开解密
        $result  = '';
        for($i = 0; $i < strlen($content)/128; $i++  ) {
            $data = substr($content, $i * 128, 128);
            openssl_private_decrypt($data, $decrypt, $res);
            $result .= $decrypt;
        }
        openssl_free_key($res);
        return $result;
    }
}
