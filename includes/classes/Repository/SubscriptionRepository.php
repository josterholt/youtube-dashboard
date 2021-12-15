<?php
namespace josterholt\Repository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\Fetch;

class SubscriptionRepository extends YouTubeRepository {
    protected static $_type = 'subscription';


    public static function getAll(): array {
        $fetch = new Fetch();
        $fetch->setRedisClient(RedisService::getInstance());

        self::$_useCache? $fetch->enableCache() : $fetch->disableCache();
    
        // Fetch channel subscriptions of authenticated user.
        $results = $fetch->get('josterholt.youtube.subscriptions', '.', function ($queryParams) {
            $queryParams['mine'] = true;


            // @todo allow Google service to be assigned outside of this
            return GoogleService::getInstance()->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
        });

        foreach ($results as $result) {
            foreach ($result->items as $item) {
                $subscriptions[] = $item;
            }
        }
        return $subscriptions;
    }    
}