<?
require_once("includes/header.php");
use \josterholt\Repository\SubscriptionRepository;
use \josterholt\Repository\ChannelRepository;
use \josterholt\Repository\PlayListItemRepository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\GoogleAPIFetch;

$fetch = new GoogleAPIFetch(RedisService::getInstance());
$fetch->enableReadCache();
$fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.

// @todo there needs to be a more graceful way to handle no service.
GoogleService::initialize();
if(GoogleService::getInstance() == null) {
    die("Unable to connect to Google Service\n");
}

$subscriptionRepository = new SubscriptionRepository();
$subscriptionRepository->disableReadCache();
$subscriptions = $subscriptionRepository->getAll();

$channelRepository = new ChannelRepository();
$channelRepository->disableReadCache();

$playListItemRepository = new PlayListItemRepository();
$playListItemRepository->disableReadCache();

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
        $playListItemRepository = new PlayListItemRepository();
        $playListItemRepository->getByPlayListId($upload_playlist_id);
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}