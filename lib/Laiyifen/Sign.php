<?php
/**
 * 
 * 
 * @author young <youngyang@icatholic.net.cn>
 *
 */
namespace Laiyifen;


class Sign
{

    private $_accessToken = null;

    private $_snsAccessToken = null;

    private $_openid = null;

    private $_request = null;

    private $_signature = null;

    private $_verifyToken = null;

    public function __construct()
    {}

    /**
     * 获取服务端的accessToken
     *
     * @throws Exception
     */
    public function getAccessToken()
    {
        if (empty($this->_accessToken)) {
            throw new Exception("请设定access_token");
        }
        return $this->_accessToken;
    }

    /**
     * 设定服务端的access token
     *
     * @param string $accessToken            
     */
    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
        $this->initRequest();
        return $this;
    }

    /**
     * 获取openid
     *
     * @throws Exception
     */
    public function getOpenId()
    {
        if (empty($this->_openid))
            throw new Exception('请设定OpenId');
        return $this->_openid;
    }
    
    
    /**
     * 设定服务端的access token
     *
     * @param string $accessToken
     */
    public function setOpenId($openId)
    {
        $this->_openid = $openId;
        return $this;
    }

    /**
     * 初始化认证的http请求对象
     */
    private function initRequest()
    {
        $this->_request = new Request($this->getAccessToken());
    }

    /**
     * 获取请求对象
     *
     * @return \Laiyifen\Http\Request
     */
    public function getRequest()
    {
        if (empty($this->_request)) {
            throw new Exception('尚未初始化request对象，请确认是否设定了access token');
        }
        return $this->_request;
    }


    /**
     * 设置用户授权的token信息
     *
     * @param string $accessToken            
     * @return \Laiyifen\Client
     */
    public function setSnsAccessToken($accessToken)
    {
        $this->_snsAccessToken = $accessToken;
        return $this;
    }

    /**
     * 获取用户授权的token信息
     *
     * @throws Exception
     */
    public function getSnsAccessToken()
    {
        if (empty($this->_snsAccessToken))
            throw new Exception('尚未设定用户的授权access token');
        return $this->_snsAccessToken;
    }

    /**
     * 获取SNS用户管理器
     *
     * @return \Laiyifen\Manager\Sns\User
     */
    public function getSnsManager()
    {
        $client = clone $this;
        $client->setAccessToken($client->getSnsAccessToken());
        return new SnsUser($client);
    }

    

    /**
     * 标准化处理微信的返回结果
     */
    public function rst($rst)
    {
        return $rst;
    }

    public function __destruct()
    {}
}