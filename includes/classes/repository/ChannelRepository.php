<?php
namespace josterholt\repository;

use josterholt\service\GoogleService;

class ChannelRepository extends YouTubeRepository {
    protected static $_type = "channel";
    protected $_readAdapter = null;

    public function setReadAdapter($readAdapter) {
        $this->_readAdapter = $readAdapter;
    }

    public function getBySubscriptionId(string $subscription_id): array {
        try {
            // TODO: Is there a way to pull channels in bulk?
            // TODO: This should throw an informative exception if readAdapter is not set.
            $channels = $this->_readAdapter->get("josterholt.youtube.channels.{$subscription_id}", '.', function ($queryParams) use($subscription_id)  {
                $queryParams = [
                    'id' => $subscription_id
                ];
                return GoogleService::getInstance()->channels->listChannels('snippet,contentDetails,statistics,contentOwnerDetails', $queryParams);
            });
        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        return $channels;
    }
}