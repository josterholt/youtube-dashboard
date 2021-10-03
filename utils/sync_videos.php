<?
require_once("includes/header.php");

/**
 * ======================================================
 * BEGIN SETUP
 * ======================================================
 */
$client = getClient();
$service = new Google\Service\YouTube($client); // Keep this accessible to other functions for reuse.
$subscriptions = getSubscriptions($service, true);

$redis = getReJSONClient($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

$fetch = new Fetch();
$fetch->setRedisClient($redis);

$channels_lookup = [];
$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    if($subscription->snippet->title == "IAmTimCorey") {
        print_r($subscription);
    }
    try {

        // @todo is there a way to pull channels in bulk?
        $channels = $fetch->get("josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}", '.', function ($queryParams) use ($service, $subscription) {
            $queryParams = [
                'id' => $subscription->snippet->resourceId->channelId
            ];
            return $service->channels->listChannels('snippet,contentDetails,statistics,contentOwnerDetails', $queryParams);
        }, FETCH_REFRESH_CACHE);
    } catch(Exception $e) {
        echo $e->getMessage();
    }

    if(empty($channels)) {
        continue;
    }


    $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
    try {
        $videos = $fetch->get("josterholt.youtube.playlistItems.{$upload_playlist_id}", '.', function ($queryParams) use ($service, $upload_playlist_id) {
            // echo $upload_playlist_id . "<br />\n";
            $queryParams = [
                'maxResults' => 25,
                'playlistId' => $upload_playlist_id
            ];

            return $service->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
        }, FETCH_REFRESH_CACHE);
    } catch(Exception $e) {
        echo "josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}\n";        
        echo "Playlist josterholt.youtube.playlistItems.{$upload_playlist_id}\n";        
        echo $e->getMessage();
    }
}