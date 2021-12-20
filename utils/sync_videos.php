<?
use \josterholt\Repository\SubscriptionRepository;
use \josterholt\Repository\ChannelRepository;
use \josterholt\Repository\PlayListItemRepository;

require_once("includes/bootstrap.php");

// TODO: Wrap this is in a class/controller
$logger = $container->get("Psr\Log\LoggerInterface");

$logger->debug("Getting all subscriptions");
$subscriptionRepository = $container->get(SubscriptionRepository::class);
$subscriptionRepository->disableReadCache();
$subscriptions = $subscriptionRepository->getAllSubscriptions();

$channelRepository = $container->get(ChannelRepository::class);
$channelRepository->disableReadCache();

$playListItemRepository = $container->get(PlayListItemRepository::class);
$playListItemRepository->disableReadCache();

$channels_lookup = [];
$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    $logger->debug("Fetching channel by subscription ID: {$subscription->snippet->resourceId->channelId}");
    $channelRepo = $container->get(ChannelRepository::class);
    $channels = $channelRepo->getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }

    try {
        $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
        $playListItemRepository = $container->get(PlayListItemRepository::class);

        $logger->debug("Upload Playlist ID: {$upload_playlist_id}\n");
        $playListItemRepository->getByPlayListId($upload_playlist_id);
    } catch (\Exception $e) {
        $logger->error("Error: {$e->getMessage()}\n $e->getTraceAsString()");
    }
}