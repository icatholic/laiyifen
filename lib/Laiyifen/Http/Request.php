<?php
/**
 * 处理HTTP请求
 * 
 * 使用Guzzle http client库做为请求发起者，以便日后采用异步请求等方式加快代码执行速度
 * 
 * @author young <youngyang@icatholic.net.cn>
 *
 */
namespace Laiyifen\Http;

use Laiyifen\Exception;
use Guzzle\Http\Client;
use Guzzle\Http\Message\PostFile;
use Guzzle\Http\ReadLimitEntityBody;

class Request
{
//     private $_serviceBaseUrl = 'https://os.laiyifen.cn/';
    private $_serviceBaseUrl = 'https://beta.os.laiyifen.cn/';

    private $_serviceBaseUrl2 = 'http://beta.os.laiyifen.cn/';

    private $_snsBaseUrl = 'https://beta.os.laiyifen.cn/';
    
    /**
     * 24小时token
     * @var unknown
     */
    private $_accessToken = null;

    /**
     * 30天token
     * @var unknown
     */
    private $_refresh_token = null;
    
    private $_tmp = null;

    public function __construct($accessToken)
    {
        $this->_accessToken = $accessToken;
        if (empty($this->_accessToken)) {
            throw new Exception("access_token为空");
        }
    }

    
    /**
     * 获取来伊份服务器信息
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function get($url, $params = array())
    {
        if ($url == 'sns/userinfo') {
            $client = new Client($this->_snsBaseUrl);
        } else {
            $client = new Client($this->_serviceBaseUrl);
        }
        $params['access_token'] = $this->_accessToken;
        $request = $client->get($url, array(), array(
            'query' => $params
        ));
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        $response = $client->send($request);
        if ($response->isSuccessful()) {
            return $response->json();
        } else {
            throw new Exception("来伊份服务器未有效的响应请求");
        }
    }


    public function __destruct()
    {
        
    }
}