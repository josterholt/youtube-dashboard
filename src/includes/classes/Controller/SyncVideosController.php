<?php

/**
 * @description Updates REDIS database with videos from YouTube API.
 * @package     josterholt\Controller
 * @author      Justin Osterholt
 * @category    Utility Class
 * @link        N/A
 * @license     MIT
 */

namespace josterholt\Controller;

use \josterholt\Repository\SubscriptionRepository;
use \josterholt\Repository\ChannelRepository;
use \josterholt\Repository\PlayListItemRepository;
use Psr\Log\LoggerInterface;


class SyncVideosController
{
    /**
     * @Inject
     * @var    SubscriptionRepository
     */
    private $_subscriptionRepository;

    /**
     * @Inject
     * @var    ChannelRepository
     */
    private $_channelRepository;

    /**
     * @Inject
     * @var    PlaylistItemRepository
     */
    private $_playListItemRepository;

    protected $logger;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sync($channel_id = null)
    {
        if (empty($channel_id)) {
            $this->logger->debug("Starting video sync.");
        } else {
            $this->logger->debug("Starting video sync for {$channel_id}.");
        }

        $subscriptions = $this->_subscriptionRepository->getAllSubscriptions();

        $subscription_channel_ids = [];
        foreach ($subscriptions as $subscription) {
            $sub_channel_id = $subscription->snippet->resourceId->channelId;
            if (empty($channel_id) || $channel_id === $sub_channel_id) {
                $subscription_channel_ids[] = $sub_channel_id;
            }
        }

        if (empty($subscription_channel_ids)) {
            return;
        }

        $channels_by_id = $this->_channelRepository->getBySubscriptionIds($subscription_channel_ids);

        foreach ($channels_by_id as $channel) {
            try {
                $upload_playlist_id = $channel->contentDetails->relatedPlaylists->uploads;
                $this->logger->debug("Upload Playlist ID: {$upload_playlist_id}\n");
                $this->_playListItemRepository->getByPlayListId($upload_playlist_id);
            } catch (\Exception $e) {
                $this->logger->error("Error: {$e->getMessage()}\n {$e->getTraceAsString()}");
            }
        }
    }
}
