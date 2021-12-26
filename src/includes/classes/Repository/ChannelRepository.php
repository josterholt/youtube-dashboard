<?php
namespace josterholt\Repository;

use josterholt\Service\GoogleService;

class ChannelRepository extends YouTubeRepository {
    protected $_type = "channel";

    public function getBySubscriptionId(string $subscription_id): array {
        try {
            // TODO: Is there a way to pull channels in bulk?
            // TODO: This should throw an informative exception if readAdapter is not set.
            $channels = $this->_readAdapter->get("youtube.channels.{$subscription_id}", '.', function ($queryParams) use($subscription_id)  {
                $queryParams = [
                    'id' => $subscription_id
                ];
                return $this->_service->getYouTubeAPIService()->channels->listChannels('snippet,contentDetails,statistics,contentOwnerDetails', $queryParams);
            });
        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        return $channels;
    }
}