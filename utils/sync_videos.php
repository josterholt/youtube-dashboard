<?
require_once("includes/header.php");


SubscriptionRepository::disableCache();
$subscriptions = SubscriptionRepository::getAll();



$channels_lookup = [];
$videos_lookup = [];
foreach ($subscriptions as  $subscription) {
    $channels = ChannelRepository::getBySubscriptionId($subscription->snippet->resourceId->channelId);

    if(empty($channels)) {
        continue;
    }

    $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
    PlayListItemRepository::getByPlayListId($upload_playlist_id);
}