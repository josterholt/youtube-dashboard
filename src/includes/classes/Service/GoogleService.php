<?php
/**
 * Wrapper for Google Service and YouTube API.
 */
namespace josterholt\Service;

use \Google\Service\YouTube;
use \Google\Client;
use Psr\Log\LoggerInterface;

/**
 * Utility class for holding Google API client
 * and methods to fetch service for API calls.
 * 
 * Use:
 * 1. GoogleService::initialize();
 * 2. GoogleService::getYouTubeAPIService();
 */
class GoogleService
{
    /**
     * Instance of Google Client used to with APIs.
     * 
     * @var Client
     */
    protected $client = null;

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
     * Indicates whether or not user has been authenticated.
     */
    public $isAuthenticated = false;

    /**
     * Constructor for GoogleService class.
     * 
     * @param Client          $client           Instance of Google Client
     * @param string          $clientSecretPath Path to client secret, Google client
     * @param string          $accessTokenPath  Path to auth token, used for read 
     *                                          and writing.
     * @param LoggerInterface $logger           Instance of a logger
     * 
     * @return void
     */
    public function __construct(Client $client, string $clientSecretPath,
        string $accessTokenPath, LoggerInterface $logger
    ) {
        $this->client = $client;       
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
        if (!$this->checkClientAccess($code)) {
            $this->isAuthenticated = false;
        } else {
            $this->isAuthenticated = true;
        }
    }

    /**
     * Initializes Google client.
     * 
     * @return void
     */
    private function _initGoogleClient()
    {
        $this->client->setApplicationName('API code samples');
        $this->client->setScopes(
            [
            'https://www.googleapis.com/auth/youtube.readonly',
            ]
        );
 
        // https://cloud.google.com/iam/docs/creating-managing-service-account-keys
        $this->logger->debug("Loading config from: ".$this->_accessTokenPath);
        $this->client->setAuthConfig($this->_clientSecretPath);
        $this->client->setAccessType('offline');
    }

    /**
     * Return instance of YouTube API service.
     * 
     * @return Youtube
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Loads access code from URL or file. Prompts for new access code if not found or expired.
     * TODO: This needs to be broken apart into smaller pieces. Redirect needs to be separated.
     * 
     * Returns true on successful token retreival and false on failure.
     * 
     * @return bool
     */
    protected function checkClientAccess($code = null): bool
    {
        $accessToken = $this->getAccessTokenFromFile($this->_accessTokenPath);
    
        if ($accessToken == null && !empty($code)) {
            $accessToken = $this->_getAccessTokenFromCode($code);
    
            if (empty($accessToken['error'])) {
                $this->storeAccessTokenToFile($this->_accessTokenPath, $accessToken);
            }
        }
    
        if (!empty($accessToken) && !empty($accessToken['error'])) {
            $accessToken = null;
        }
    
        if (empty($accessToken)) {
            return false;
        } else {
            $this->client->setAccessToken($accessToken);

            $tokenCallback = function ($cacheKey, $accessToken) {
                $accessTokenNew = $this->getAccessTokenFromFile($this->_accessTokenPath);
                $accessTokenNew['access_token'] = $accessToken;
                $this->storeAccessTokenToFile($this->_accessTokenPath, $accessTokenNew);
                $this->client->setAccessToken($accessToken);
            };
            $this->client->setTokenCallback($tokenCallback);
        }
        return true;
    }

    /**
     * Checks for existing access token or code (ability to get access token).
     * If one does not exist, redirects user to authorization page.
     *
     * @return bool True if redirect header is set
     */
    public function getAuthorizationPageURL(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get access token from query string.
     * 
     * @param Client $client Google client for API connection.
     * @param string $code Code from query string.
     * 
     * @return ?array Access token granted from Google API using code.
     */
    private function _getAccessTokenFromCode(string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        return $this->client->fetchAccessTokenWithAuthCode(trim($code));
    }

    /**
     * Store access token to file for later use.
     * 
     * @param string     $filePath    Path to token file
     * @param array|null $accessToken Access token data to store
     * 
     * @return bool
     */
    protected function storeAccessTokenToFile(
        string $filePath, array|null $accessToken
    ): bool {
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
