<?php
/**
 * Wrapper for Google Service and YouTube API.
 */
namespace josterholt\Service;

use \Google\Service\YouTube;
use \Google\Client;
use Psr\Log\LoggerInterface;

/**
 * Singleton utility class for holding Google API client
 * and methods to fetch service for API calls.
 * 
 * Use:
 * 1. GoogleService::initialize();
 * 2. GoogleService::getService();
 */
class GoogleService
{
    /**
     * Instance of Google Client used to with APIs.
     * 
     * @var \Google\Client
     */
    private $_client = null;

    /**
     * Instance of YouTubeAPI service.
     * 
     * @var YouTube
     */
    protected $youTubeAPIService = null;

    /**
     * Path to client secret file.
     * 
     * @var string
     */
    private $_clientSecretPath = null;

    /**
     * Path to access token file.
     * 
     * @var string
     */
    private $_accessTokenPath = null;

    /**
     * Instance of a logger.
     * 
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Constructor for GoogleService class.
     * 
     * @param Client          $client             Instance of Google Client
     * @param string          $clientSecretPath   Path to client secret, Google client
     * @param string          $accessTokenPath    Path to auth token, used for read and writing
     * @param LoggerInterface $logger             Instance of a logger
     * 
     * @return void
     */
    public function __construct(Client $client, string $clientSecretPath,
        string $accessTokenPath, LoggerInterface $logger
    ) {
        $this->_client = $client;
        $this->_clientSecretPath = $clientSecretPath;
        $this->_accessTokenPath = $accessTokenPath;
        $this->logger = $logger;
    }

    /**
     * Initializes Google client and YouTube API service
     * 
     * @return void
     */
    public function initialize($code = null)
    {
        $this->_initGoogleClient();
        $this->checkClientAccess($code);
        $this->youTubeAPIService = new YouTube($this->_client);
    }

    /**
     * Initializes Google client.
     * 
     * @return void
     */
    private function _initGoogleClient()
    {
        $this->_client->setApplicationName('API code samples');
        $this->_client->setScopes(
            [
            'https://www.googleapis.com/auth/youtube.readonly',
            ]
        );
 
        // https://cloud.google.com/iam/docs/creating-managing-service-account-keys
        $this->logger->debug("Loading config from: ".$this->_accessTokenPath);
        $this->_client->setAuthConfig($this->_clientSecretPath);
        $this->_client->setAccessType('offline');
    }

    /**
     * Return instance of YouTube API service.
     * 
     * @return Youtube
     */
    public function getYouTubeAPIService()
    {
        return $this->youTubeAPIService;
    }

    /**
     * Loads access code from URL or file. Prompts for new access code if not found or expired.
     * TODO: This needs to be broken apart into smaller pieces. Redirect needs to be separated.
     * 
     * @return void
     */
    protected function checkClientAccess($code = null): bool
    {
        $accessToken = $this->getAccessTokenFromFile($this->_accessTokenPath);
    
        if ($accessToken == null && !empty($code)) {
            $accessToken = $this->_getAccessTokenFromCode($this->_client, $code);
    
            if (empty($accessToken['error'])) {
                $this->_storeAccessTokenToFile($this->_accessTokenPath, $accessToken);
            }
        }
    
        if (!empty($accessToken) && !empty($accessToken['error'])) {
            $accessToken = null;
        }
    
        // TODO: Look into handling token when it has expired and client doesn't autorenew
        if (empty($accessToken)) {
            return false;
            // TODO: This is a code smell. Method should return an expected value and shouldn't die.
            //$this->_redirectToAuthorizationPage();
        } else {
            $this->_client->setAccessToken($accessToken);

            $tokenCallback = function ($cacheKey, $accessToken) {
                $accessTokenNew = $this->getAccessTokenFromFile($this->_accessTokenPath);
                $accessTokenNew['access_token'] = $accessToken;
                $this->_storeAccessTokenToFile($this->_accessTokenPath, $accessTokenNew);
                $this->_client->setAccessToken($accessToken);
            };
            $this->_client->setTokenCallback($tokenCallback);
        }
        return true;
    }

    /**
     * Checks for existing access token or code (ability to get access token).
     * If one does not exist, redirects user to authorization page.
     *
     * @return bool True if redirect header is set
     */
    private function _redirectToAuthorizationPage(): void
    {
        $authUrl = $this->_client->createAuthUrl();
        $this->logger->debug("Auth URL: " . $authUrl);
        //echo "URL: {$authUrl}<br />\n";
        //header("Location: {$authUrl}");
    }

    /**
     * Get access token from query string.
     * 
     * @param Client $client Google client for API connection.
     * @param string $code Code from query string.
     * 
     * @return ?array Access token granted from Google API using code.
     */
    private static function _getAccessTokenFromCode(Client $client, string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        return $client->fetchAccessTokenWithAuthCode(trim($code));
    }

    /**
     * Store access token to file for later use.
     * 
     * @param string     $filePath    Path to token file
     * @param array|null $accessToken Access token data to store
     * 
     * @return bool
     */
    private function _storeAccessTokenToFile(string $filePath, array|null $accessToken): bool
    {
        if (file_put_contents($filePath, json_encode($accessToken)) === false) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves access token from file.
     * 
     * @param string $filePath Path to token file
     * 
     * @return array
     */
    protected function getAccessTokenFromFile(string $filePath): array|null
    {
        if (!file_exists($filePath)) {
            return null;
        }

        return (array) json_decode(file_get_contents($filePath));
    }
}
