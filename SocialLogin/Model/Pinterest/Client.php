<?php
namespace Magenest\SocialLogin\Model\Pinterest;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Pinterest
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#cb2027';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'pinterest';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/pinterest/connect/';
    /**
     * @var string
     */
    protected $path_enalbed       = 'magenest/credentials/pinterest/enabled';
    /**
     * @var string
     */
    protected $path_client_id     = 'magenest/credentials/pinterest/client_id';
    /**
     * @var string
     */
    protected $path_client_secret = 'magenest/credentials/pinterest/client_secret';
    /**
     * @var string
     */
    protected $oauth2_service_uri = 'https://api.pinterest.com/v1';
    /**
     * @var string
     */
    protected $oauth2_auth_uri  = 'https://api.pinterest.com/oauth';
    /**
     * @var string
     */
    protected $oauth2_token_uri = 'https://api.pinterest.com/v1/oauth/token';
    /**
     * @var string[]
     */
    protected $scope = ['read_public', 'write_public'];

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        $query = [
            'response_type' => 'code' ,
            'client_id' => $this->getClientId(),
            'state' => $this->getState(),
            'scope' => implode(' ', $this->getScope()),
            'redirect_uri' => $this->getRedirectUri()
        ];
        $url = $this->oauth2_auth_uri . '?' . http_build_query($query);
        return $url;
    }

    /**
     * @param null $code
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function fetchAccessToken($code = null)
    {
        $token_array = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $code
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
