<?php
/**
 * 来伊份客服端请求
 * 
 *
 */
namespace Laiyifen;

class Client
{

    private $_isProduct = true;

    public function setIsProduct($isProduct)
    {
        $this->_isProduct = $isProduct;
    }
    
    // 服务地址
    private $_laiyifen_url4_test = 'http://beta.os.laiyifen.cn/';

    private $_laiyifen_url = 'http://os.laiyifen.com/';

    public function getLaiyifenUrl()
    {
        if ($this->_isProduct) {
            return $this->_laiyifen_url;
        } else {
            return $this->_laiyifen_url4_test;
        }
    }

    private $_client_id;

    public function setClientId($client_id)
    {
        $this->_client_id = $client_id;
    }

    public function getClientId()
    {
        if (empty($this->_client_id)) {
            throw new Exception('请设定$client_id');
        }
        return $this->_client_id;
    }

    private $_client_secret;

    public function setClientSecret($client_secret)
    {
        $this->_client_secret = $client_secret;
    }

    public function getClientSecret()
    {
        if (empty($this->_client_secret)) {
            throw new Exception('请设定$client_secret');
        }
        
        return $this->_client_secret;
    }

    private $_private_key_path;

    /**
     * 设定第3方的rsa私钥路径 例如国泰
     *
     * @param string $private_key_path            
     */
    public function setPrivateKeyPath($private_key_path)
    {
        $this->_private_key_path = $private_key_path;
    }

    public function getPrivateKeyPath()
    {
        if (empty($this->_private_key_path)) {
            throw new Exception('请设置第3方私钥');
        }
        return $this->_private_key_path;
    }

    private $_public_key_path;

    /**
     * 设定来伊份rsa公钥路径
     *
     * @param string $public_key_path            
     */
    public function setPublicKeyPath($public_key_path)
    {
        $this->_public_key_path = $public_key_path;
    }

    public function getPublicKeyPath()
    {
        if (empty($this->_public_key_path)) {
            throw new Exception('请设置来伊份公钥');
        }
        return $this->_public_key_path;
    }

    public function __construct($client_id, $client_secret, $private_key_path, $public_key_path)
    {
        $this->setClientId($client_id);
        $this->setClientSecret($client_secret);
        $this->setPrivateKeyPath($private_key_path);
        $this->setPublicKeyPath($public_key_path);
    }

    /**
     * 标准化处理微信的返回结果
     */
    public function rst($rst)
    {
        return $rst;
    }

    /**
     * 绑定卡券
     *
     * 请求参数
     * 参数	必填	类型	说明
     * client_id	是	string	应用key
     * client_secret	是	string	应用的App Secret
     * sign_type	是	string	RSA固定值，必须大写
     * sign	是	string	签名
     * response_type	是	string	coupon，固值
     * coupon_no	是	string	优惠券号
     * passwd	是	string	优惠券号密码
     * mobile	是	string	会员手机号
     *
     * 成功返回：
     *
     * {"rsp":"succ","code":0,"data":{"servertime":"1434735786","msg":"\u7ed1\u5b9a\u6210\u529f","sign":"W5dilZNx3IKxQL30nYDi4dIkIygvhMaTnLsy\/lWg2ceEEe2BjprObKCUZjsp0wAqOPuoN8L+vIM+\/HNAK9WZKCulNBSdA28ORS2aw1KGdHTGZ\/i2mEZUKaFTxwoFJRk3X5yxr+KiLX0RcH+41+ndJJ0BoY4Tansga+3NsI56XW0="}}
     *
     * @param string $coupon_no            
     * @param string $passwd            
     * @param string $mobile            
     * @param string $remark            
     */
    public function couponBind($coupon_no, $passwd, $mobile, $remark = "")
    {
        $params = array(
            'method' => 'coupon.bind',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'response_type' => 'coupon',
            'coupon_no' => $coupon_no,
            'passwd' => $passwd,
            'mobile' => $mobile,
            'remark' => $remark
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }

    /**
     * 请求RSA验签
     *
     * @param array $params            
     * @throws Exception
     */
    public function clientRSASign(array $params)
    {
        // 待签名字符串
        $data = Helpers::getSignParam($params);
        // 请求时签名
        $sign = Helpers::rsaSign($data, $this->getPrivateKeyPath());
        $params = $params + array(
            'sign_type' => 'RSA',
            'sign' => $sign
        );
        $url = $this->getLaiyifenUrl();
        $url = $url . "index.php/open/pcart-newapi";
        
        $response = $this->get($url, $params);
        $response = json_decode($response, true);
        // '{"rsp":"fail","code":1000,"data":"\u5361\u5238\u4e0d\u5b58\u5728\u6216\u5df2\u8fc7\u671f"}'
        if (isset($response['rsp']) && $response['rsp'] == 'fail') {
            throw new Exception($response['data'], $response['code']);
        }
        
        // 通知返回时验证签名
        $responseSign = $response['data']['sign'];
        $response_param = Helpers::getSignParam($response['data']);
        $verify_result = Helpers::rsaVerify($response_param, $this->getPublicKeyPath(), $responseSign);
        if ($verify_result) {
            return $response;
        } else {
            throw new Exception('通知返回时验证签名失败');
        }
    }

    /**
     * 获取微信服务器信息
     *
     * @param string $url            
     * @param array $params            
     * @param array $options            
     * @return mixed
     */
    public function get($url, $params = array(), $options = array())
    {
        $client = new \Guzzle\Http\Client();
        $request = $client->get($url, array(), array(
            'query' => $params
        ), $options);
        
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        $response = $client->send($request);
        if ($response->isSuccessful()) {
            return $response->getBody(true);
        } else {
            throw new Exception("来伊份服务器未有效的响应请求");
        }
    }

    /**
     * 推送消息给到微信服务器
     *
     * @param string $url            
     * @param string $body            
     * @param array $options            
     * @return mixed
     */
    public function post($url, $body, $options = array())
    {
        $client = new \Guzzle\Http\Client();
        $client->setDefaultOption('query', array(
            'access_token' => $this->getAccessToken()
        ));
        $client->setDefaultOption('body', $body);
        $request = $client->post($url, null, null, $options);
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        $response = $client->send($request);
        if ($response->isSuccessful()) {
            return $response->getBody(true);
        } else {
            throw new Exception("来伊份服务器未有效的响应请求");
        }
    }

    /**
     * 品牌接口
     *
     * 1、品牌接口
     * 1.1品牌列表【kjt.brand.list】
     * 输入参数
     * page_no Int(10) 1 页码
     * page_size Int(10) 20 每页显示数据
     * 输出参数
     * total Int(10) 100 总条数
     * page_no Int(10) 1 页码
     * page_size Int(10) 20 每页显示数据
     * items(详细列表)
     * brand_id Int(10) 1 ID
     * brand_name Varchar(200)始袓鸟 名称
     * brand_url Varchar(200)品牌 URL
     * brand_desc Varchar(200) 描述
     * brand_log Varchar(200) LOGO
     * brand_keywords Varchar(200) 关键字
     */
    public function kjtBrandList($page_no = 1, $page_size = 20)
    {
        $params = array(
            'method' => 'kjt.brand.list',
            'page_no' => $page_no,
            'page_size' => $page_size
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }

    /**
     * 跨境通类别列表
     * 
     * @param number $page_no           页码
     * @param number $page_size         每页显示数据
     */
    public function kjtCategoryList($page_no = 1, $page_size = 20)
    {
        $params = array(
            'method' => 'kjt.category.list',
            'page_no' => $page_no,
            'page_size' => $page_size
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    /**
     * 跨境通类别详情
     * @param number $category_id
     */
    public function kjtCategoryDetail($category_id)
    {
        $params = array(
            'method' => 'kjt.category.detail',
            'category_id' => $category_id
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    /**
     * 跨境通供应商列表
     * @param number $page_no
     * @param number $page_size
     */
    public function kjtVendorList($page_no = 1, $page_size = 20)
    {
        $params = array(
            'method' => 'kjt.vendor.list',
            'page_no' => $page_no,
            'page_size' => $page_size
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    /**
     * 
     * @param number $vendor_id
     */
    public function kjtVendorDetail($vendor_id)
    {
        $params = array(
            'method' => 'kjt.vendor.detail',
            'vendor_id' => $vendor_id
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    /**
     * 跨境通产品列表
     * @param number $page_no       页码  1
     * @param number $page_size     每页显示数据 20
     * @param string $orderby       倒序排序：默认uptime(上架时间)、downtime(下架时间)、last_modify（最后修改时间）
     * @param number $cat_id        分类ID默认:0
     * @param number $brand_id      品牌ID默认:0
     * @param number $bprice        起始价格(与结束价格共同作用) 单位分，送参换算成 元单位
     * @param number $eprice        结束价格(与起始价格共同作用)
     * @param string $name          搜索名称
     */
    public function kjtProductList($page_no = 1, $page_size = 20, $name = '', $orderby = '', $cat_id = 0, $brand_id = 0, $bprice=0, $eprice=0)
    {
        $params = array(
            'method' => 'kjt.brand.list',
            'page_no' => $page_no,
            'page_size' => $page_size
        );
        if(!empty($orderby)){
            $params['orderby'] = $orderby;
        }
        if(!empty($cat_id)){
            $params['cat_id'] = $cat_id;
        }
        if(!empty($brand_id)){
            $params['brand_id'] = $brand_id;
        }
        if(!empty($bprice) && !empty($eprice)){
            $params['bprice'] = $bprice / 100;
            $params['eprice'] = $eprice / 100;
        }
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    /**
     *  根据产品ID获取产品详细
     * @param number $goods_id
     */
    public function kjtProductDetail($goods_id)
    {
        $params = array(
            'method' => 'kjt.product.detail',
            'goods_id' => $goods_id
        );
        $ret = $this->clientRSASign($params);
        return $this->rst($ret['data']);
    }
    
    
    public function __destruct()
    {}
}