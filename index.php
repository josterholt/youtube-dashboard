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
 * BEGIN FETCH DATA
 */
$category_title_lookup = [];
$data = $redis->get("categories.names");
foreach($data as $category) {
    $category_title_lookup[$category->id]['categoryTitle'] = $category->title;
}

$item_category_lookup = [];
$data = $redis->getArray("categories.items"); // @todo Store categories in user specific namespace
foreach ($data['mapping'] as $map) {
    if(empty($map['itemID'])) {
        continue;
    }

    $category_title = "None";
    if(isset($category_title_lookup[$map['categoryID']])) {
        $category_title = $category_title_lookup[$map['categoryID']]['categoryTitle'];
    }
    $item_category_lookup[$map['itemID']] = ["categoryID" => $map['categoryID'], "categoryTitle" => $category_title];
}

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
/**
 * END FETCH DATA
 */

/**
 * BEGIN PAGE CONTENT
 */
$channel_sets = [];
foreach ($subscriptions as  $subscription) {
    $displayed_channels[] = $subscription->snippet->resourceId->channelId;
    $videos = $videos_lookup[$subscription->snippet->resourceId->channelId];

    $last_activity = "Last Activity: ";
    if (isset($lastActivityLookup[$subscription->snippet->resourceId->channelId])) {
        $last_activity .= date('m/d/y', $lastActivityLookup[$subscription->snippet->resourceId->channelId]);
    } else {
        $last_activity .= "N/A";
    }

    if(!isset($item_category_lookup[MD5($subscription->snippet->resourceId->channelId)])) {
        $category = ["categoryID" => 0, "categoryTitle" => "None"];
    } else {
        $category = $item_category_lookup[MD5($subscription->snippet->resourceId->channelId)];
        echo "<pre>";
        print_r($category);
        echo "</pre>";
    }

    $grouped_channel_sets[$category['categoryID']]['category'] = $category;
    $grouped_channel_sets[$category['categoryID']]['items'][] = ["subscription" => $subscription, "videos" => $videos, "last_activity" => $last_activity];
}

$context = [
    "grouped_channel_sets" => $grouped_channel_sets,
];
echo $twig->render("index.twig", $context);
/**
 * ======================================================
 * END PAGE CONTENT
 * ======================================================
 */
