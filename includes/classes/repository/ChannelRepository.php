<?php
namespace josterholt\repository;
use josterholt\service\Fetch;
use josterholt\service\RedisService;
use josterholt\service\GoogleService;

class ChannelRepository extends YouTubeRepository {
    protected static $_type = "channel";

    public static function getBySubscriptionId(string $subscription_id): array {
        $fetch = new Fetch();
        self::$_useCache? $fetch->enableCache() : $fetch->disableCache();
        $fetch->setRedisClient(RedisService::getInstance()); // This shouldn't be hardcoded.
        
        try {
            // @todo is there a way to pull channels in bulk?
            $channels = $fetch->get("josterholt.youtube.channels.{$subscription_id}", '.', function ($queryParams) use($subscription_id)  {
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