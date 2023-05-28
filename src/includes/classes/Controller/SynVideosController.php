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

        foreach ($subscriptions as  $subscription) {
            // Skip sync if a syncing a specific ID and current channelId doesn't match
            if (!empty($channel_id) && $channel_id != $subscription->snippet->resourceId->channelId) {
                continue;
            }

            $this->logger->debug("Fetching channel by subscription ID: {$subscription->snippet->resourceId->channelId}");
            $channels = $this->_channelRepository->getBySubscriptionId($subscription->snippet->resourceId->channelId);

            if (empty($channels)) {
                continue;
            }

            try {
                $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;

                $this->logger->debug("Upload Playlist ID: {$upload_playlist_id}\n");
                $this->_playListItemRepository->getByPlayListId($upload_playlist_id);
            } catch (\Exception $e) {
                $this->logger->error("Error: {$e->getMessage()}\n $e->getTraceAsString()");
            }
        }
    }
}
