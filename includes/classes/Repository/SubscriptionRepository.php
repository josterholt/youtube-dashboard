<?php
namespace josterholt\Repository;
use josterholt\Service\GoogleService;
use josterholt\Service\RedisService;
use josterholt\Service\GoogleAPIFetch;

class SubscriptionRepository extends YouTubeRepository {
    protected $_type = 'subscription';
    protected $_readAdapter = null;

    public function __construct() {
        $this->_setupRedis();
    }

    protected function _setupRedis() {
        $this->_readAdapter = $this->_getReadAdapter();
    }

    public function _getReadAdapter() {
        return new GoogleAPIFetch(RedisService::getInstance()); // This is a code smell. Redis should be injected.
    }

    public function getAll(): array {
        //$this->_useCache? $this->_fetch->enableReadCache() : $fetch->disableReadCache();
    
        // Fetch channel subscriptions of authenticated user.
        $results = $this->_readAdapter->get('josterholt.youtube.subscriptions', '.', function ($queryParams) {
            $queryParams['mine'] = true;


            // @todo allow Google service to be assigned outside of this
            return GoogleService::getInstance()->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
        });

        $subscriptions = [];
        if($results) {
            foreach ($results as $result) {
                if($result->items) {
                    foreach ($result->items as $item) {
                        $subscriptions[] = $item;
                    }
                }
            }
        }

        return $subscriptions;
    }    
}