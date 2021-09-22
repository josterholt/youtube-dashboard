<?php

use Redislabs\Module\ReJSON\ReJSON;

/**
 * Checks for existing access token or code (ability to get access token).
 * If one does not exist, redirects user to authorization page.
 * @return bool True if redirect header is set
 */
function redirectToAuthorizationPage(\Google\Client $client): void
{
    $authUrl = $client->createAuthUrl();
    header("Location: {$authUrl}");
}

function getAccessTokenFromCode(\Google\Client $client, string $code): ?array
{
    if (empty($code)) {
        return null;
    }

    $authCode = trim($code);

    // Exchange authorization code for an access token.
    return $client->fetchAccessTokenWithAuthCode($authCode); // @todo returns array?
}

function storeAccessTokenToFile(string $file_path, string $access_token): bool
{
    if (file_put_contents($file_path, json_encode($access_token)) === false) {
        return false;
    }

    return true;
}

function getAccessTokenFromFile(string $file_path)
{
    if (!file_exists($file_path)) {
        return null;
    }

    return (array) json_decode(file_get_contents($file_path));
}

function getGoogleClient(): \Google\Client
{
    $client = new Google_Client();
    $client->setApplicationName('API code samples');
    $client->setScopes([
        'https://www.googleapis.com/auth/youtube.readonly',
    ]);

    // More Info: https://cloud.google.com/iam/docs/creating-managing-service-account-keys
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType('offline');
    return $client;
}

/**
 * Returns a ReJSON client. Connection will auto-close at end of script.
 * @param string $url
 * @param string $port
 * @param string $password
 * @return Redislabs\Module\ReJSON\ReJSON
 */
function getReJSONClient(string $url, int $port, string $password = null): Redislabs\Module\ReJSON\ReJSON
{
    $redisClient = new Redis();
    $redisClient->connect($url, $port);
    if ($password != null) {
        $redisClient->auth($password);
    }

    register_shutdown_function(function () use ($redisClient) {
        $redisClient->close();
    });

    return ReJSON::createWithPhpRedis($redisClient);
}

function transposeSubscriptionToRedisStruct($subscription)
{
    $subscription = (object) [
        "subscriptionId" => $subscription->id,
        "channelId" => $subscription->snippet->resourceId->channelId,
        "channelTitle" => $subscription->snippet->title,
        "description" => $subscription->snippet->description,
        "thumbnail" => (object) [
            "defaultURL" => $subscription->snippet->thumbnails->default->url,
            "mediumURL" => $subscription->snippet->thumbnails->medium->url,
            "highURL" => $subscription->snippet->thumbnails->high->url
        ]
    ];
    return $subscription;
}

class Fetch
{
    private $_redis = null;

    /**
     * @param string $url
     * @param int $port
     * @param string $password
     */
    public function setupRedisCache(?string $url = "localhost", ?int $port = 6379, ?string $password = null)
    {
        $this->_redis = getReJSONClient($url, $port, $password);
    }

    /**
     * @param string $key
     * @param string $path
     * @param string $query
     * @param $forceRefresh
     * @return array of responses
     */
    public function get($key, $path, $query, $forceRefresh = false)
    {
        if (!$forceRefresh) {
            $cache = $this->_redis->get($key, $path);
            if (!empty($cache)) {
                //echo "Using cache for {$key}<br />\n";
                return json_decode($cache);
            }
        }


        $loop = true;
        $queryParams = ['maxResults' => 500];
        $responses = [];
        while ($loop) {
            $response = $query($queryParams);
            if (empty($response)) {
                $loop = false;
                continue;
            } else {
                $responses[] = $response->toSimpleObject();
            }


            // Setup next page
            if (empty($response->getNextPageToken()) || (isset($queryParams['pageToken']) && $response->getNextPageToken() == $queryParams['pageToken'])) {
                $loop = false;
            } else {
                $queryParams['pageToken'] = $response->getNextPageToken();
            }
        }
        $this->_redis->set($key, $path, json_encode($responses)); // Support array of requests

        return $responses;
    }
}
