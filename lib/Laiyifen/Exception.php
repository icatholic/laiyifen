<?php
namespace Laiyifen;

/**
 * Base exception class for Weixin-related errors.
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class Exception extends \Exception
{

    private $_errorInfo = array(
        "-1" => "系统繁忙",
        "0" => "请求成功"
    );

    public function getErrorMsg($errcode)
    {
        if (key_exists($errcode, $this->_errorInfo)) {
            return $this->_errorInfo[$errcode];
        } else {
            return "unknown error";
        }
    }
}
