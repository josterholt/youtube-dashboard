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

$fetch = new Fetch();
$fetch->setupRedisCache($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

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
?>
<html>

<head>
    <title>YouTube Dashboard</title>
    <link type="text/css" rel="stylesheet" href="css/main.css" />
</head>

<body>
    <?
    $service = new YouTube($client); // Keep this accessible to other functions for reuse.

    // Fetch channel subscriptions of authenticated user.
    $results = $fetch->get('josterholt.youtube.subscriptions', '.', function ($queryParams) use ($service) {
        $queryParams['mine'] = true;
        return $service->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
    });

    foreach ($results as $result) {
        foreach ($result->items as $item) {
            $subscriptions[] = $item;
        }
    }


    echo "<ul class='subscription-list'>";
    foreach ($subscriptions as  $subscription) {

        $results = $fetch->get("josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}", '.', function ($queryParams) use ($service, $subscription) {
            $queryParams = [
                'id' => $subscription->snippet->resourceId->channelId
            ];
            return $service->channels->listChannels('snippet,contentDetails,statistics', $queryParams);
        });

        $upload_playlist_id = $results[0]->items[0]->contentDetails->relatedPlaylists->uploads;


        $results = $fetch->get("josterholt.youtube.playlistItems.{$upload_playlist_id}", '.', function ($queryParams) use ($service, $upload_playlist_id) {
            echo $upload_playlist_id . "<br />\n";
            $queryParams = [
                'maxResults' => 25,
                'playlistId' => $upload_playlist_id
            ];

            $results = [];
            try {
                $results = $service->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            return $results;
        });


        echo "<li><img src=\"{$subscription->snippet->thumbnails->default->url}\" /> {$subscription->snippet->title} ({$subscription->snippet->resourceId->channelId})</li>\n";
        if (!empty($results)) {
            foreach ($results[0]->items as $item) {
                echo $item->snippet->title . "<br />\n";
            }
        }
    }
    echo "</ul>";
    ?>
</body>

</html>