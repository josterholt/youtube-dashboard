<?php
namespace josterholt\service;

/**
 * Singleton utility class for holding Google API client
 * and methods to fetch service for API calls.
 * 
 * Use:
 * 1. GoogleService::initialize();
 * 2. GoogleService::getService();
 */
class GoogleService {
    protected static $_instance = null;

    public static function initialize() {
        self::getInstance();
    }

    public static function getInstance() {
        if(self::$_instance == null) {
            static::$_instance = new \Google\Service\YouTube(self::getClient());
        }

        return static::$_instance;
    }

    protected static function getGoogleClient(): \Google\Client
    {
        $client = new \Google_Client();
        $client->setApplicationName('API code samples');
        $client->setScopes([
            'https://www.googleapis.com/auth/youtube.readonly',
        ]);

        // More Info: https://cloud.google.com/iam/docs/creating-managing-service-account-keys
        $client->setAuthConfig('client_secret.json');
        $client->setAccessType('offline');
        return $client;
    }       

    protected static function getClient()
    {
        $client = self::getGoogleClient();

        $accessToken = self::getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);
    
        if ($accessToken == null && !empty($_GET['code'])) {
            $accessToken = self::getAccessTokenFromCode($client, $_GET['code']);
    
            if(empty($accessToken['error'])) {
                self::storeAccessTokenToFile($_ENV['ACCESS_TOKEN_FILE_PATH'], $accessToken);
            }
        }
    
        /**
         * @todo This could possibly cause a redirect loop. It should be altered to avoid this scenario.
         */
        if (!empty($accessToken) && !empty($accessToken['error'])) {
            $accessToken = null;
        }
    
        if (empty($accessToken)) {
            // This is a code smell. Method should return an expected value and shouldn't die.
            self::redirectToAuthorizationPage($client);
            die();
        } else {
            $client->setAccessToken($accessToken);

            if($client->isAccessTokenExpired()) {
                
            }

            $tokenCallback = function ($cacheKey, $accessToken) use ($client){
                $accessTokenNew = self::getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);
                $accessTokenNew['access_token'] = $accessToken;
                self::storeAccessTokenToFile($_ENV['ACCESS_TOKEN_FILE_PATH'], $accessTokenNew);
                echo sprintf("**** New access token received at cache key %s and access token %s\n\n", $cacheKey, $accessToken);                
                $client->setAccessToken($accessToken);

            };
            $client->setTokenCallback($tokenCallback);
        }
    
        return $client;
    }

    /**
     * Checks for existing access token or code (ability to get access token).
     * If one does not exist, redirects user to authorization page.
     * @return bool True if redirect header is set
     */
    protected static function redirectToAuthorizationPage(\Google\Client $client): void
    {
        $authUrl = $client->createAuthUrl();
        header("Location: {$authUrl}");
    }

    protected static function getAccessTokenFromCode(\Google\Client $client, string $code): ?array
    {
        if (empty($code)) {
            return null;
        }

        $authCode = trim($code);

        // Exchange authorization code for an access token.
        return $client->fetchAccessTokenWithAuthCode($authCode); // @todo returns array?
    }

    protected static function storeAccessTokenToFile(string $file_path, array|null $access_token): bool
    {
        if (file_put_contents($file_path, json_encode($access_token)) === false) {
            return false;
        }

        return true;
    }

    protected static function getAccessTokenFromFile(string $file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        return (array) json_decode(file_get_contents($file_path));
    }
}