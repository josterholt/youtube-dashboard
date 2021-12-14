<?
require_once("includes/header.php");
use \josterholt\repository\SubscriptionRepository;
use \josterholt\repository\ChannelRepository;
use \josterholt\repository\PlayListItemRepository;
use josterholt\service\GoogleService;
use josterholt\service\RedisService;
use josterholt\service\Fetch;

$fetch = new Fetch();
$fetch->enableCache();
$fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.

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
    $channelRepo = new ChannelRepository();
    $channelRepo->setReadAdapter($fetch);
    $channels = $channelRepo->getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }

    try {
        $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
        PlayListItemRepository::getByPlayListId($upload_playlist_id);
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}