<?php

namespace Magenest\SocialLogin\Model\Twitter;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Twitter
 */
class Client
{
    /**
     *
     */
    const CHART_COLOR = '#2eb7eb';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN       = 'twitter';
    /**
     *
     */
    const REDIRECT_URI_ROUTE      = 'sociallogin/twitter/connect';
    /**
     *
     */
    const REQUEST_TOKEN_URI_ROUTE = 'sociallogin/twitter/request';
    /**
     *
     */
    const XML_PATH_ENABLED       = 'magenest/credentials/twitter/enabled';
    /**
     *
     */
    const XML_PATH_CLIENT_ID     = 'magenest/credentials/twitter/client_id';
    /**
     *
     */
    const XML_PATH_CLIENT_SECRET = 'magenest/credentials/twitter/client_secret';
    /**
     *
     */
    const OAUTH2_SERVICE_URI = 'https://api.twitter.com/1.1';
    /**
     *
     */
    const OAUTH_URI        = 'https://api.twitter.com/oauth';
    /**
     *
     */
    const OAUTH2_TOKEN_URI = 'https://api.twitter.com/oauth2/token';

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var null
     */
    protected $isEnabled = null;

    /**
     * @var mixed|null
     */
    protected $clientId = null;

    /**
     * @var mixed|null
     */
    protected $clientSecret = null;

    /**
     * @var null|string
     */
    protected $redirectUri = null;

    /**
     * @var null
     */
    protected $token = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Zend_Oauth_Consumer
     */
    protected $client;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * Client constructor.
     *
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\UrlInterface $url,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->serializer         = $serialize;
        $this->_httpClientFactory = $httpClientFactory;
        $this->_config            = $config;
        $this->_url               = $url;
        $this->redirectUri        = $this->_url->getBaseUrl() . self::REDIRECT_URI_ROUTE;
        $this->clientId           = $this->_getClientId();
        $this->clientSecret       = $this->_getClientSecret();
        $this->_config            = $config;
        $this->_customerSession   = $customerSession;
        $this->client             = new \Zend_Oauth_Consumer(
            [
                'callbackUrl'    => $this->redirectUri,
                'siteUrl'        => self::OAUTH_URI,
                'authorizeUrl'   => self::OAUTH_URI . '/authenticate',
                'consumerKey'    => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_isEnabled();
    }

    /**
     * @return mixed|null
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return mixed|null
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return null|string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param \StdClass $token
     */
    public function setAccessToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return bool|string
     * @throws LocalizedException
     * @throws \Zend_Oauth_Exception
     */
    public function getAccessToken()
    {
        if (empty($this->token)) {
            $this->fetchAccessToken();
        }

        return $this->serializer->serialize($this->token);
    }

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->_url->getUrl(self::REQUEST_TOKEN_URI_ROUTE);
    }

    /**
     * @param $path
     * @param string $method
     * @param array $params
     *
     * @return mixed
     * @throws LocalizedException
     * @throws \Exception
     */
    public function api($path, $getParams, $method = 'GET', $params = [])
    {
        if (empty($this->token)) {
            $this->fetchAccessToken($getParams);
        }
        $url      = self::OAUTH2_SERVICE_URI . $path;
        $response = $this->_httpRequest($url, strtoupper($method), $params);

        return $response;
    }

    /**
     * @return \Zend_Oauth_Token_Access
     * @throws LocalizedException
     * @throws \Zend_Oauth_Exception
     */
    protected function fetchAccessToken($getParams)
    {
        if (!($params = $getParams) || !($requestToken = $this->_customerSession->getTwitterRequestToken())) {
            throw new LocalizedException(__('Unable to retrieve access code.'));
        }

        $tokenObj = new \Zend_Oauth_Token_Request();
        $tokenObj->setParams(json_decode($requestToken, true));

        if (!($token = $this->client->getAccessToken($params, $tokenObj))) {
            throw new LocalizedException(__('Unable to retrieve access token.'));
        }

        $this->_customerSession->unsTwitterRequestToken();

        return $this->token = $token;
    }

    /**
     * @param $url
     * @param string $method
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    protected function _httpRequest($url, $method = 'GET', $params = [])
    {
        $client = $this->token->getHttpClient(
            [
                'callbackUrl'    => $this->redirectUri,
                'siteUrl'        => self::OAUTH_URI,
                'consumerKey'    => $this->clientId,
                'consumerSecret' => $this->clientSecret
            ]
        );

        $client->setUri($url);

        switch ($method) {
            case 'GET':
                $client->setMethod(\Zend_Http_Client::GET);
                $client->setParameterGet($params);
                break;
            case 'POST':
                $client->setMethod(\Zend_Http_Client::POST);
                $client->setParameterPost($params);
                break;
            case 'DELETE':
                $client->setMethod(\Zend_Http_Client::DELETE);
                break;
            default:
                throw new \Exception(__('Required HTTP method is not supported.'));
        }
        $response         = $client->request();
        $decoded_response = json_decode($response->getBody(), true);

        return $decoded_response;
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchRequestToken()
    {
        if (!($requestToken = $this->client->getRequestToken())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve request token.'));
        }
        $tokenData = [
            'oauth_token' => $requestToken->getParam('oauth_token'),
            'oauth_token_secret' => $requestToken->getParam('oauth_token_secret'),
            'oauth_callback_confirmed' => $requestToken->getParam('oauth_callback_confirmed')
        ];

        $this->_customerSession->setTwitterRequestToken(json_encode($tokenData));
        $this->client->redirect();
    }

    /**
     * @return mixed
     */
    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    /**
     * @return mixed
     */
    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    /**
     * @return mixed
     */
    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    /**
     * @param $xmlPath
     *
     * @return mixed
     */
    protected function _getStoreConfig($xmlPath)
    {
        return $this->_config->getValue($xmlPath);
    }
}
