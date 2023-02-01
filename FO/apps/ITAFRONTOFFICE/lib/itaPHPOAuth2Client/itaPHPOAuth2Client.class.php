<?php

/**
 *
 * Classe per collegamento rest servoice
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPRestClient
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    20.12.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
class itaPHPOAuth2Client {

    private $clientId;
    private $clientSecret;
    private $urlAuthorize;
    private $urlAccessToken;
    private $urlResourceOwnerDetails;
    private $scope;

    function getClientId() {
        return $this->clientId;
    }

    function getClientSecret() {
        return $this->clientSecret;
    }

    function getUrlAuthorize() {
        return $this->urlAuthorize;
    }

    function getUrlAccessToken() {
        return $this->urlAccessToken;
    }

    function getUrlResourceOwnerDetails() {
        return $this->urlResourceOwnerDetails;
    }

    function getScope() {
        return $this->scope;
    }

    function setClientId($clientId) {
        $this->clientId = $clientId;
    }

    function setClientSecret($clientSecret) {
        $this->clientSecret = $clientSecret;
    }

    function setUrlAuthorize($urlAuthorize) {
        $this->urlAuthorize = $urlAuthorize;
    }

    function setUrlAccessToken($urlAccessToken) {
        $this->urlAccessToken = $urlAccessToken;
    }

    function setUrlResourceOwnerDetails($urlResourceOwnerDetails) {
        $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    }

    function setScope($scope) {
        $this->scope = $scope;
    }

    public function getToken() {

        /*
         * GenericProvider
         */
//        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
//            'clientId' => 'f23b7c95-0e22-4112-9b1e-3ab859f06178', // The client ID assigned to you by the provider
//            'clientSecret' => 'ibMjcT19kXetkYqDcZg3a90wHbMUP0gWRLrDaufCbRA=', // The client password assigned to you by the provider
//            'urlAuthorize' => 'https://login.microsoftonline.com/21e6c8ed-d2ec-4d8d-bc73-599d1c25f58a/oauth2/v2.0/authorize',
//            'urlAccessToken' => 'https://login.microsoftonline.com/21e6c8ed-d2ec-4d8d-bc73-599d1c25f58a/oauth2/v2.0/token',
//            'urlResourceOwnerDetails' => ''
//        ]);
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'urlAuthorize' => $this->urlAuthorize,
            'urlAccessToken' => $this->urlAccessToken,
            'urlResourceOwnerDetails' => ''
        ]);

        $options = [
            'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
            'scope' => $this->scope // array or string
        ];

        try {
            // Try to get an access token using the client credentials grant.
            $accessToken = $provider->getAccessToken('client_credentials', $options);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

            // Failed to get the access token
            exit($e->getMessage());
        }

        /*
         * Microsoft
         */
//        $provider = new Stevenmaguire\OAuth2\Client\Provider\Microsoft([
//            'clientId' => 'f23b7c95-0e22-4112-9b1e-3ab859f06178', // The client ID assigned to you by the provider
//            'clientSecret' => 'ibMjcT19kXetkYqDcZg3a90wHbMUP0gWRLrDaufCbRA=', // The client password assigned to you by the provider
//            'redirectUri' => 'https://login.microsoftonline.com/21e6c8ed-d2ec-4d8d-bc73-599d1c25f58a/oauth2/v2.0/uri', // The client password assigned to you by the provider
//            'urlAuthorize' => 'https://login.microsoftonline.com/21e6c8ed-d2ec-4d8d-bc73-599d1c25f58a/oauth2/v2.0/authorize',
//            'urlAccessToken' => 'https://login.microsoftonline.com/21e6c8ed-d2ec-4d8d-bc73-599d1c25f58a/oauth2/v2.0/token'
//        ]);
//
//        try {
//            $options = [
//                'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
//                'scope' => 'http://api.civilianextuat.it/.default' // array or string
//            ];
//            $accessToken = $provider->getAccessToken('client_credentials', $options);
//            Out::msgInfo("accessToken", $accessToken);
//        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
//            // Failed to get the access token
//            exit($e->getMessage());
//        }
        
        return $accessToken;
    }

}
