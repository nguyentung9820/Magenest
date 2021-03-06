<?php
namespace Magenest\SocialLogin\Model\Instagram;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Instagram
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#fdf497';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'instagram';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/instagram/connect/';
    /**
     * @var string
     */
    protected $path_enalbed       = 'magenest/credentials/instagram/enabled';
    /**
     * @var string
     */
    protected $path_client_id     = 'magenest/credentials/instagram/client_id';
    /**
     * @var string
     */
    protected $path_client_secret = 'magenest/credentials/instagram/client_secret';
    /**
     * @var string
     */
    protected $oauth2_service_uri = 'https://api.instagram.com/v1';
    /**
     * @var string
     */
    protected $oauth2_auth_uri  = 'https://api.instagram.com/oauth/authorize';
    /**
     * @var string
     */
    protected $oauth2_token_uri = 'https://api.instagram.com/oauth/access_token';
    /**
     * @var string[]
     */
    protected $scope = ['basic'];

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        $query = [
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'scope' => implode(',', $this->getScope()),
            'response_type' => "code"
        ];
        $url = $this->oauth2_auth_uri . '?' . http_build_query($query);
        return $url;
    }

    /**
     * @param $endpoint
     * @param $code
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws LocalizedException
     */
    public function api($endpoint, $code, $method = 'GET', $params = [])
    {
        if (empty($this->token)) {
            $this->fetchAccessToken($code);
        }
        $url = $this->oauth2_service_uri . $endpoint.$this->token;
        
        $method = strtoupper($method);
        $params = array_merge([
                'access_token' => $this->token
        ], $params);
        $response = $this->_httpRequest($url, $method, $params);
        return $response;
    }

    /**
     * @param null $code
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function fetchAccessToken($code = null)
    {
        $token_array = [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'grant_type'=>'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
            'code' => $code,
        ];

        if (empty($code)) {
            throw new LocalizedException(
                __('Unable to retrieve access code.')
            );
        }
        $response = $this->_httpRequest(
            $this->oauth2_token_uri,
            'POST',
            $token_array
        );
        $this->setAccessToken($response['access_token']);
        return $this->getAccessToken();
    }
}
