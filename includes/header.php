<?php

/**
 * BEGIN AUTOLOAD SCRIPTS
 */
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/../vendor/autoload.php';
/**
 * END AUTOLOAD SCRIPTS
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once("functions.php");

$fetch = new Fetch();
$fetch->setupRedisCache($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

/**
 * @todo move this out of header.php. Google API won't be used for all files.
 */
$client = getGoogleClient();
$accessToken = getAccessTokenFromFile($_ENV['ACCESS_TOKEN_FILE_PATH']);

if ($accessToken == null && !empty($_GET['code'])) {
    $accessToken = getAccessTokenFromCode($client, $_GET['code']);
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
