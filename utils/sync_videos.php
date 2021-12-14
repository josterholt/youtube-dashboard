<?
require_once("includes/header.php");
use \josterholt\repository\SubscriptionRepository;
use \josterholt\repository\ChannelRepository;
use \josterholt\repository\PlayListItemRepository;
use josterholt\service\GoogleService;

// @todo there needs to be a more graceful way to handle no service.
GoogleService::initialize();
if(GoogleService::getInstance() == null) {
    die("Unable to connect to Google Service\n");
}

SubscriptionRepository::disableCache();
ChannelRepository::disableCache();
PlayListItemRepository::disableCache();

$subscriptions = SubscriptionRepository::getAll();


$channels_lookup = [];
$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    $channels = ChannelRepository::getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }
    echo ".";

    try {
        $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
        PlayListItemRepository::getByPlayListId($upload_playlist_id);
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}