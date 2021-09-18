<?php

use Google\Service\YouTube;


/**
 * BEGIN AUTOLOAD SCRIPTS
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/vendor/autoload.php';
/**
 * END AUTOLOAD SCRIPTS
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once("functions.php");

$redis = getReJSONClient($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);
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
}
?>
<html>

<head>
    <title>YouTube Dashboard</title>
    <style>
        .subscription-list li img {
            vertical-align: middle;
        }

        .subscription-list li {
            list-style: none;
            margin-top: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <?
    $source = "Cache";
    $structs = $redis->get('josterholt.youtube.subscriptions');
    if (empty($structs)) {
        $source = "YouTube API";
        $structs = [];
        if (!empty($accessToken)) {
            $client->setAccessToken($accessToken);
            $service = new YouTube($client);
            $subscriptions = getSubscriptions($service);

            foreach ($subscriptions as  $subscription) {
                $structs[] = transposeSubscriptionToRedisStruct($subscription);
            }
            $redis->set('josterholt.youtube.subscriptions', '.', $structs);
        }
    }

    echo "Source: {$source}\n";

    echo "<ul class='subscription-list'>";
    foreach ($structs as  $sub) {
        echo "<li><img src=\"{$sub->thumbnail->defaultURL}\" /> {$sub->channelTitle}</li>\n";
    }
    echo "</ul>";
    ?>
</body>

</html>
<?php
$redis->close();
?>