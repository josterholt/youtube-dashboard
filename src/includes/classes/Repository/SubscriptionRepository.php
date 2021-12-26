<?php
namespace josterholt\Repository;


class SubscriptionRepository extends YouTubeRepository
{
    protected $_type = 'subscription';
    protected $_readAdapter = null;

    /**
     *  Fetch channel subscriptions of authenticated user.
     *
     * @return array
     */ 
    public function getAllSubscriptions(): array
    {
        return $this->processResults($this->getSubscriptionsFromAPI());
    }

    /**
     * Queries YouTube API for subscriptions. A paginated list of subscriptions are returned.
     * Expected results from _readAdapter is the paginated result set of API response from Google.
     * 
     * Subscription List Response Spec: https://developers.google.com/youtube/v3/docs/subscriptions/list
     * 
     * @return array
     * [
     *     {SubscriptionListResponse(s)},
     * ]
     */
    public function getSubscriptionsFromAPI(): array
    {
        return $this->_readAdapter->get(
            'youtube.subscriptions', '.', function ($queryParams) {
                $queryParams["mine"] = true;
            
                $subscriptions = $this->_service->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
                $this->_logger->debug("Fetched " . count($subscriptions) . " subscriptions.");
                return $subscriptions;
            }
        );
    }


    /**
     * Merges paginated API results into a flat list of subscriptions.
     * 
     * TODO: This needs to be refactored so that data shape conversion is done elsewhere.
     * (Might make more sense for this to be in the read adapter)
     * 
     * Subscription List Response Spec: https://developers.google.com/youtube/v3/docs/subscriptions/list
     * Subscriptions Spec: https://developers.google.com/youtube/v3/docs/subscriptions
     * 
     * @param array $results
     * [
     *     SubscriptionListResponse(s)
     * ]
     * 
     * @return array
     * [
     *    {
     *        items: [
     *            Subscription Resource Object(s)
     *        ]
     *    }
     * ]
     */
    protected function processResults(Array $results)
    {       
        $subscriptions = [];
        if ($results) {
            foreach ($results as $result) {
                if ($result->items) {
                    foreach ($result->items as $item) {
                        $subscriptions[] = $item;
                    }
                }
            }
        }

        return $subscriptions;
    }
}
