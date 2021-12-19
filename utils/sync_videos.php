<?
use \josterholt\Repository\SubscriptionRepository;
use \josterholt\Repository\ChannelRepository;
use \josterholt\Repository\PlayListItemRepository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\GoogleAPIFetch;

require_once("includes/bootstrap.php");

// @todo there needs to be a more graceful way to handle no service.
// GoogleService::initialize();
// if(GoogleService::getInstance() == null) {
//     die("Unable to connect to Google Service\n");
// }

$subscriptionRepository = $container->get(SubscriptionRepository::class);
$subscriptionRepository->disableReadCache();
$subscriptions = $subscriptionRepository->getAll();

$channelRepository = $container->get(ChannelRepository::class);
$channelRepository->disableReadCache();

$playListItemRepository = $container->get(PlayListItemRepository::class);
$playListItemRepository->disableReadCache();

$channels_lookup = [];
$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    $channelRepo = $container->get(ChannelRepository::class);
    $channels = $channelRepo->getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }

    try {
        $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
        $playListItemRepository = $container->get(PlayListItemRepository::class);
        $playListItemRepository->getByPlayListId($upload_playlist_id);
    } catch (\Exception $e) {
        echo $e->getMessage();
        echo $e->getTraceAsString();
    }
}