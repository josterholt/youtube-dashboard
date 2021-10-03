<?php
require_once("classes/Fetch.php");

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

function storeAccessTokenToFile(string $file_path, array|null $access_token): bool
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

    return Redislabs\Module\ReJSON\ReJSON::createWithPhpRedis($redisClient);
}

function getTwig()
{
    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader);
    return $twig;
}

function getClient()
{
    /**
     * @todo move this out of header.php. Google API won't be used for all files.
     */
    $client = getGoogleClient();
    $accessToken = getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);

    if ($accessToken == null && !empty($_GET['code'])) {
        $accessToken = getAccessTokenFromCode($client, $_GET['code']);

        if(empty($accessToken['error'])) {
            storeAccessTokenToFile($_ENV['ACCESS_TOKEN_FILE_PATH'], $accessToken);
        }
    }

    /**
     * @todo This could possibly cause a redirect loop. It should be altered to avoid this scenario.
     */
    if (!empty($accessToken) && !empty($accessToken['error'])) {
        $accessToken = null;
    }

    if (empty($accessToken)) {
        redirectToAuthorizationPage($client);
        die();
    } else {
        $client->setAccessToken($accessToken);
    }

    return $client;
}

function getSubscriptions($service, $forceRefresh=false)
{
    $fetch = new Fetch();
    $fetch->setupRedisCache($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

    // Fetch channel subscriptions of authenticated user.
    $results = $fetch->get('josterholt.youtube.subscriptions', '.', function ($queryParams) use ($service) {
        $queryParams['mine'] = true;
        return $service->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
    }, $forceRefresh);

    foreach ($results as $result) {
        foreach ($result->items as $item) {
            $subscriptions[] = $item;
        }
    }
    return $subscriptions;
}