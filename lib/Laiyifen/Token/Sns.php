<?php
namespace Laiyifen\Token;

use Laiyifen\Exception;

class Sns
{
    // 服务地址
    private $_laiyifen_url = 'http://beta.os.laiyifen.cn/';
    // private $_laiyifen_url = 'http://os.laiyifen.com/';
    private $_client_id;

    private $_client_secret;

    private $_redirect_uri;

    private $_scope = 'basic';

    private $_state = '';

    private $_display = 'default';

    private $_token;

    private $_openid;

    private $_context;

    public function __construct($client_id, $client_secret)
    {
        if (empty($client_id)) {
            throw new Exception('请设定$client_id');
        }
        if (empty($client_secret)) {
            throw new Exception('请设定$client_secret');
        }
        
        $this->_client_id = $client_id;
        $this->_client_secret = $client_secret;
        $opts = array(
            'http' => array(
                'follow_location' => 3,
                'max_redirects' => 3,
                'timeout' => 10,
                'method' => "GET",
                'header' => "Connection: close\r\n",
                'user_agent' => 'iCatholic R&D'
            )
        );
        $this->_context = stream_context_create($opts);
    }

    /**
     * 设定回调地址
     *
     * @param string $redirect_uri            
     * @throws Exception
     */
    public function setRedirectUri($redirect_uri)
    {
        // $redirect_uri = trim(urldecode($redirect_uri));
        $redirect_uri = trim($redirect_uri);
        if (filter_var($redirect_uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('$redirect_uri无效');
        }
        $this->_redirect_uri = urlencode($redirect_uri);
    }

    /**
     * 设定作用域类型
     *
     * @param string $scope            
     * @throws Exception
     */
    public function setScope($scope)
    {
        if (! in_array($scope, array(
            'basic'
        ), true)) {
            throw new Exception('$scope无效');
        }
        $this->_scope = $scope;
    }

    /**
     * 设定携带参数信息，请使用rawurlencode编码
     *
     * @param string $state            
     */
    public function setState($state)
    {
        $this->_state = $state;
    }

    /**
     * 登录和授权页面的展现样式，默认为“default”或空
     *
     * @param string $display            
     * @throws Exception
     */
    public function setDisplay($display)
    {
        if (! in_array($display, array(
            'default'
        ), true)) {
            throw new Exception('$display无效');
        }
        $this->_display = $display;
    }

    /**
     * 获取认证地址的URL
     */
    public function getAuthorizeUrl()
    {
        $url = $this->_laiyifen_url . "oauth2/authorize?client_id={$this->_client_id}&redirect_uri={$this->_redirect_uri}&response_type=code&scope={$this->_scope}&state={$this->_state}&display={$this->_display}";
        header("location:{$url}");
        exit();
    }

    /**
     * 获取access token
     *
     * @throws Exception
     * @return array
     */
    public function getAccessToken()
    {
        $code = isset($_GET['code']) ? trim($_GET['code']) : '';
        if ($code == '') {
            throw new Exception('code不能为空');
        }
        $url = $this->_laiyifen_url . "oauth2/access_token?client_id={$this->_client_id}&client_secret={$this->_client_secret}&grant_type=authorization_code&code={$code}&redirect_uri={$this->_redirect_uri}";
        $response = file_get_contents($url, false, $this->_context);
        $response = json_decode($response, true);
        
        return $response;
    }

    /**
     * 通过refresh token获取新的access token
     */
    public function getRefreshToken($refresh_token, $scope)
    {
        $response = file_get_contents($this->_laiyifen_url . "oauth2/access_token?client_id={$this->_client_id}&grant_type=refresh_token&refresh_token={$refresh_token}&scope={$scope}&client_secret={$this->_client_secret}", false, $this->_context);
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * 获得OPENID
     */
    public function getOpenId($access_token)
    {
        $url = $this->_laiyifen_url . "oauth2/get_openid?client_id={$this->_client_id}&client_secret={$this->_client_secret}&access_token={$access_token}";
        $response = file_get_contents($url, false, $this->_context);
        $response = json_decode($response, true);
        
        return $response;
    }

    public function __destruct()
    {}
}