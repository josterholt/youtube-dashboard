<?
require_once("includes/header.php");

/**
 * ======================================================
 * BEGIN SETUP
 * ======================================================
 */
$client = getClient();
$service = new Google\Service\YouTube($client); // Keep this accessible to other functions for reuse.
$subscriptions = getSubscriptions($service);
$twig = getTwig();

/**
 * ======================================================
 * END SETUP
 * ======================================================
 */


/**
 * ======================================================
 * BEGIN PAGE CONTENT
 * ======================================================
 */

/**
 * BEGIN UTIL SETUP
 */
$redis = getReJSONClient($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

$fetch = new Fetch();
$fetch->setRedisClient($redis);
/**
 * END UTIL SETUP
 */

// Store last activity for display
$lastActivityLookup = [];

/**
 * BEGIN MAIN CONTENT
 */
$category_lookup = [];
$data = $redis->getArray("categories.items"); // @todo Store categories in user specific namespace
foreach ($data['mapping'] as $map) {
    $category_lookup[$map['itemID']] = $map['categoryID'];
}

// $data = $redis->get("categories.names");
// echo "<pre>";
// print_r($data);
// echo "</pre>";

$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    $channels = $fetch->get("josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}", '.', function ($queryParams) use ($service, $subscription) {
        $queryParams = [
            'id' => $subscription->snippet->resourceId->channelId
        ];
        return $service->channels->listChannels('snippet,contentDetails,statistics', $queryParams);
    });

    $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;

    $videos = $fetch->get("josterholt.youtube.playlistItems.{$upload_playlist_id}", '.', function ($queryParams) use ($service, $upload_playlist_id) {
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
    $videos_lookup[$subscription->snippet->resourceId->channelId] = $videos;
}

$content = "";
$content = "<div>" . count($subscriptions) . " subscriptions</div>\n";
$content .= "<ul class='formatted-list'>";
foreach ($subscriptions as  $subscription) {
    $videos = $videos_lookup[$subscription->snippet->resourceId->channelId];

    $video_html = "";
    if (!empty($videos)) {
        $video_html .= "<div style=\"margin-bottom: 25px;\"><button class='js-video-list-toggle video-toggle' js-channelId='{$subscription->snippet->resourceId->channelId}'>Show Videos</button></div>\n";
        $video_html .= "<ul class='formatted-list' style='display: none' id='js-video-list-{$subscription->snippet->resourceId->channelId}'>";
        foreach ($videos[0]->items as $video) {
            if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($video->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                $lastActivityLookup[$subscription->snippet->resourceId->channelId] = strtotime($video->snippet->publishedAt);
            }

            $formatted_date_str = date('m-d-Y', strtotime($video->snippet->publishedAt));
            $video_html .= "<li><img src='{$video->snippet->thumbnails->default->url}' />" . $video->snippet->title . " (Published: {$formatted_date_str})</li>\n";
        }
        $video_html .= "</ul>";
    }

    $last_activity_str = "Last Activity: ";
    if (isset($lastActivityLookup[$subscription->snippet->resourceId->channelId])) {
        $last_activity_str .= date('m/d/y', $lastActivityLookup[$subscription->snippet->resourceId->channelId]);
    } else {
        $last_activity_str .= "N/A";
    }

    $context = ["subscription" => $subscription];
    $content .= "<li>
        <img src=\"{$subscription->snippet->thumbnails->default->url}\" /> {$subscription->snippet->title} ({$last_activity_str})
        " . $twig->render("video_categories.twig", $context) . "
    </li>\n";
    $content .= "<!-- {$subscription->snippet->resourceId->channelId} -->\n";

    $content .= $video_html;
}
$content .= "</ul>";

$context = [
    "video_list_content" => $content,
];
echo $twig->render("index.twig", $context);
/**
 * ======================================================
 * END PAGE CONTENT
 * ======================================================
 */
