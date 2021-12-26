<?php
namespace josterholt\Service;

/**
 * Singleton utility class for holding Google API client
 * and methods to fetch service for API calls.
 * 
 * Use:
 * 1. GoogleService::initialize();
 * 2. GoogleService::getService();
 */
class GoogleService {
    /**
     * @var \Google\Client
     */
    protected $_client = null;

    /**
     * @var \Google\Service\YouTube
     */
    protected $_youTubeAPIService = null;

    public function initialize() {
        $this->_client = new \Google\Client();
        $this->_client->setApplicationName('API code samples');
        $this->_client->setScopes([
            'https://www.googleapis.com/auth/youtube.readonly',
        ]);

        // More Info: https://cloud.google.com/iam/docs/creating-managing-service-account-keys
        $this->_client->setAuthConfig('client_secret.json');
        $this->_client->setAccessType('offline');

        $this->_checkClientAccess();


        $this->_youTubeAPIService = new \Google\Service\YouTube($this->_client);
    }

    public function getYouTubeAPIService() {
        return $this->_youTubeAPIService;
    }

    protected function _checkClientAccess()
    {
        $accessToken = $this->_getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);
    
        if ($accessToken == null && !empty($_GET['code'])) {
            $accessToken = $this->_getAccessTokenFromCode($this->_client, $_GET['code']);
    
            if(empty($accessToken['error'])) {
                $this->_storeAccessTokenToFile($_ENV['ACCESS_TOKEN_FILE_PATH'], $accessToken);
            }
        }
    
        /**
         * @todo This could possibly cause a redirect loop. It should be altered to avoid this scenario.
         */
        if (!empty($accessToken) && !empty($accessToken['error'])) {
            $accessToken = null;
        }
    
        // TODO: Look into handling token when it has expired and client doesn't autorenew
        if (empty($accessToken)) {
            // This is a code smell. Method should return an expected value and shouldn't die.
            $this->_redirectToAuthorizationPage($this->_client);
            die();
        } else {
            $this->_client->setAccessToken($accessToken);

            $tokenCallback = function ($cacheKey, $accessToken) {
                $accessTokenNew = $this->_getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);
                $accessTokenNew['access_token'] = $accessToken;
                $this->_storeAccessTokenToFile($_ENV['ACCESS_TOKEN_FILE_PATH'], $accessTokenNew);
                $this->_client->setAccessToken($accessToken);
            };
            $this->_client->setTokenCallback($tokenCallback);
        }
    }

    /**
     * Checks for existing access token or code (ability to get access token).
     * If one does not exist, redirects user to authorization page.
     * @return bool True if redirect header is set
     */
    protected function _redirectToAuthorizationPage(\Google\Client $client): void
    {
        $authUrl = $client->createAuthUrl();
        //echo "URL: {$authUrl}<br />\n";
        header("Location: {$authUrl}");
    }

    protected static function _getAccessTokenFromCode(\Google\Client $client, string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        $authCode = trim($code);

        // Exchange authorization code for an access token.
        return $client->fetchAccessTokenWithAuthCode($authCode); // @todo returns array?
    }

    protected function _storeAccessTokenToFile(string $file_path, array|null $access_token): bool
    {
        if (file_put_contents($file_path, json_encode($access_token)) === false) {
            return false;
        }

        return true;
    }

    protected function _getAccessTokenFromFile(string $file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        return (array) json_decode(file_get_contents($file_path));
    }
}