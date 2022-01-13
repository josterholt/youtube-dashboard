<?php
namespace josterholt\Repository;


class SubscriptionRepository extends AbstractYouTubeRepository
{
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
        $getSubscriptionsFromGoogleAPI = function ($queryParams) {
            $queryParams["mine"] = true;
            $subscriptions = $this->service->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
            $this->logger->debug("Fetched " . count($subscriptions) . " subscriptions.");
            return $subscriptions;
        };

        return $this->readAdapter->get(
            'youtube.subscriptions', '.', $getSubscriptionsFromGoogleAPI
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
    protected function processResults(Array $results): array
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

    public function getAll(): array
    {
        return [];
    }

    public function getById($id): object|null
    {
        return null;
    }
    
    public function create(object $record): bool
    {
        return false;
    }

    public function update(object $record): bool
    {
        return false;
    }

    public function delete($id): bool
    {
        return false;
    }
}
