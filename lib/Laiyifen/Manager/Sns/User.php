<?php
namespace Laiyifen\Manager\Sns;

use Laiyifen\Client;

/**
 */
class User
{

    private $_client;

    private $_request;

    public function __construct(Client $client)
    {
        $this->_client = $client;
        $this->_request = $client->getRequest();
    }

    public function getSnsUserInfo($accessToken, $openId)
    {
        $params = array();
        $params['openid'] = $openId;
        $params['lang'] = $lang;
        $rst = $this->_request->get('sns/userinfo', $params);
        return $this->_client->rst($rst);
    }
}
