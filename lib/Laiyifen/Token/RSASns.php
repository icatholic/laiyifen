<?php
namespace Laiyifen\Token;

use Laiyifen\Exception;

class RSASns
{

    private $_client;

    public function __construct(\Laiyifen\Client $client)
    {
        $this->_client = $client;
    }

    /**
     * rsa 加密类型 获取access token
     *
     * @param string $code            
     * @param string $redirect_uri            
     */
    public function getAccessToken($code, $redirect_uri)
    {
        $params = array(
            'client_id' => $this->_client->getClientId(),
            'client_secret' => $this->_client->getClientSecret(),
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'method' => 'oauth2.token'
        );
        
        $verify_result = $this->_client->clientRSASign($params);
        return $this->_client->rst($verify_result['data']);
    }

    /**
     * 获取RSA OPENID
     *
     * @param string $code            
     * @param string $access_token            
     */
    public function getOpenId($code, $access_token)
    {
        $params = array(
            'client_id' => $this->_client->getClientId(),
            'client_secret' => $this->_client->getClientSecret(),
            'code' => $code,
            'access_token' => $access_token,
            'method' => 'oauth2.open_id'
        );
        $verify_result = $this->_client->clientRSASign($params);
        return $this->_client->rst($verify_result['data']);
    }

    /**
     * RSA 获取用户信息
     *
     * @param string $token            
     * @param string $openid            
     */
    public function getMemberInfo($token, $open_id)
    {
        $params = array(
            'client_id' => $this->_client->getClientId(),
            'client_secret' => $this->_client->getClientSecret(),
            'token' => $token,
            'open_id' => $open_id,
            'response_type' => 'code',
            'method' => 'member.info'
        );
        $verify_result = $this->_client->clientRSASign($params);
        return $this->_client->rst($verify_result['data']);
    }

    public function __destruct()
    {}
}