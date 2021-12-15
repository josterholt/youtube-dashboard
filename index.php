<?
require_once("includes/header.php");

$twig = getTwig();


/**
 * BEGIN FETCH DATA
 */
// Store last activity for display
$lastActivityLookup = [];

$category_title_lookup = [];
$data = josterholt\Repository\CategoryRepository::getAll();
foreach($data as $category) {
    $category_title_lookup[$category->id]['categoryTitle'] = $category->title;
}

$item_category_lookup = [];
$data = josterholt\Repository\CategoryRepository::getItems();
if(!empty($data)) {
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
}

$channels_lookup = [];
$play_list_items_lookup = [];

$subscriptions = josterholt\Repository\SubscriptionRepository::getAll();
foreach ($subscriptions as  $subscription) {
    // @todo is there a way to pull channels in bulk?
    $channels = josterholt\Repository\ChannelRepository::getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }

    $channels_lookup[$subscription->snippet->resourceId->channelId] = $channels[0]->items[0];

    $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
    $play_list_items = josterholt\Repository\PlayListItemRepository::getByPlaylistId($upload_playlist_id);


    if(!empty($play_list_items)) {
        foreach ($play_list_items[0]->items as $play_list_item) {            
            if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($play_list_item->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                $lastActivityLookup[$subscription->snippet->resourceId->channelId] = strtotime($play_list_item->snippet->publishedAt);
            }
        }
    }

    $play_list_items_lookup[$subscription->snippet->resourceId->channelId] = $play_list_items;
}
/**
 * END FETCH DATA
 */

/**
 * BEGIN PAGE CONTENT
 */
$selected_category = "";
if(!empty($_GET['category']) && $_GET['category'] != 'NO_FILTER') {
    $selected_category = $_GET['category'];
}

$grouped_channel_sets = [];
foreach ($subscriptions as  $subscription) {
    $displayed_channels[] = $subscription->snippet->resourceId->channelId;
    $play_list_items = $play_list_items_lookup[$subscription->snippet->resourceId->channelId];

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
    }

    if(empty($selected_category) || $category['categoryID'] == $selected_category) {
        $grouped_channel_sets[$category['categoryID']]['category'] = $category;
        $channel = $channels_lookup[$subscription->snippet->resourceId->channelId];
        $grouped_channel_sets[$category['categoryID']]['items'][] = ["subscription" => $subscription, "channel" => $channel, "play_list_items" => $play_list_items, "last_activity" => $last_activity];
    }
}

usort($grouped_channel_sets, function ($set_a, $set_b) {   
    if($set_a['category']['categoryID'] == 0) {
        return 1;
    }

    if($set_b['category']['categoryID'] == 0) {
        return -1;
    }

    return strnatcmp($set_a['category']['categoryTitle'], $set_b['category']['categoryTitle']);
});

$context = [
    "grouped_channel_sets" => $grouped_channel_sets,
];
echo $twig->render("index.twig", $context);
/**
 * ======================================================
 * END PAGE CONTENT
 * ======================================================
 */
