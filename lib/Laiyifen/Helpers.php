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
     * 生成待签名字符串
     *
     * 待签名字符串规则：对提交数据里的每一个值从 a 到 z 的顺序排序，若遇到相同首字母，则看第二个字母，以此类推。
     * 排序完成之后，再把所有数组值以“&”字符连接起来。
     * 	没有值的参数无需传递，也无需包含到待签名数据中
     * 	签名时使用UTF8字符集
     * 	根据 HTTP协议要求，传递参数的值中如果存在特殊字符（如：&、@等），那么该值需要做 URL Encoding
     * 	在请求参数列表中，除去 sign、sign_type 两个参数外，其他需要使用到的参数皆是要签名的参数。（个别接口中参数 sign_type 也需要参与签名。）
     * 	在通知返回参数列表中，除去 sign、sign_type、resultFormat 参数外，凡是通知返回的参数皆是要签名的参数。
     * 
     * @param array $query_data            
     * @param array $excludeKeys            
     * @return string
     */
    public static function getSignParam(array $query_data, array $excludeKeys = array(
                'sign',
                'sign_type',
                'resultFormat'
            ))
    {
        foreach ($query_data as $key => $val) {
            if (empty($val) || in_array($key, $excludeKeys)) {
                unset($query_data[$key]);
            }
        }
        ksort($query_data);
        return http_build_query($query_data, null, '&', PHP_QUERY_RFC1738);
    }

    /**
     * RSA签名
     *
     * 请求时签名：
     * 当拿到请求时的待签名字符串后，把待签名字符串与客户的私钥一同放入RSA的签名函数中进行签名运算，从而得到签名结果字符串。
     *
     * @param string $data
     *            待签名数据
     * @param string $private_key_path
     *            客户私钥文件路径
     * @return string
     */
    public static function rsaSign($data, $private_key_path)
    {
        $privateFile = file_get_contents($private_key_path);
        $privateKey = openssl_get_privatekey($privateFile);
        openssl_sign($data, $sign, $privateKey);
        openssl_free_key($privateKey);
        // base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA验签
     *
     * 通知返回时验证签名：
     * 当获得到通知返回时的待签名字符串后，把待签名字符串、来伊份支付系统提供的公钥、
     * 来伊份支付系统通知返回参数中的参数sign的值三者一同放入RSA的签名函数中进行非对称的签名运算，来判断签名是否验证通过。
     *
     * @param string $data
     *            待签名数据
     * @param string $public_key_path
     *            来伊份的公钥文件路径
     * @param string $sign
     *            要校对的的签名结果
     * @return boolean
     */
    public static function rsaVerify($data, $public_key_path, $sign)
    {
        $publicFile = file_get_contents($public_key_path);
        $publicKey = openssl_get_publickey($publicFile);
        $result = (bool) openssl_verify($data, base64_decode($sign), $publicKey);
        openssl_free_key($publicKey);
        return $result;
    }
}
