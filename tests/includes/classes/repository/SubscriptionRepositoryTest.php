<?php

use Google\Collection;
use PHPUnit\Framework\TestCase;
use josterholt\Repository\SubscriptionRepository;
use josterholt\Service\GoogleAPIFetch;
use Redislabs\Module\ReJSON\ReJSON;
use josterholt\Service\GoogleService;

class SubscriptionRepositoryTest extends TestCase
{
    /**
     * @covers SubscriptionRepository
     */
    public function testGetSubscriptionsFromAPIReturnsCorrectResults()
    {
        /**
         * SETUP BEGINS
         */
        $test_subscriptions = [1, 2]; 
        

        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        
        $redis = $this->createStub(ReJSON::class);
        $googleService = $this->createStub(GoogleService::class);

        $googleAPIFetch = $this->getMockBuilder(GoogleAPIFetch::class)
            ->setConstructorArgs([$logger, $redis])
            ->getMock();

        $googleAPIFetch->method('get')
            ->willReturn($test_subscriptions);
        /**
         * SETUP ENDS
         */
        
        $subscriptionRepository = new SubscriptionRepository($logger, $googleAPIFetch, $googleService);
        $subscription_results = $subscriptionRepository->getSubscriptionsFromAPI();

        $this->assertCount(count($test_subscriptions), $subscription_results);
    }

    /**
     * Tests getSubscriptionsFromAPI for proper retrieval of data from reader adapter.
     * Results returned by mock will be the same as those returned by this method.
     * 
     * @covers SubscriptionRepository
     */
    public function testGetAllSubscriptionsFromAPI()
    {
        /**
         * SETUP BEGINS
         */
        $api_response = [
            (object) [
                "items" => [
                    [ "id" => 1 ],
                    [ "id" => 2 ],
                ],
            ],
            (object) [
                "items" => [
                    [ "id" => 3 ],
                    [ "id" => 4 ],
                ],
            ],
            (object) [
                "items" => [
                    [ "id" => 5 ],
                    [ "id" => 6 ],
                ],
            ]
        ];

        $expected_response = [
            [ "id" => 1 ],
            [ "id" => 2 ],
            [ "id" => 3 ],
            [ "id" => 4 ],
            [ "id" => 5 ],
            [ "id" => 6 ],
        ];


        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        
        $redis = $this->createStub(ReJSON::class);
        $googleService = $this->createStub(GoogleService::class);

        $googleAPIFetch = $this->getMockBuilder(GoogleAPIFetch::class)
            ->setConstructorArgs([$logger, $redis])
            ->getMock();

        $googleAPIFetch->method('get')
            ->willReturn($api_response);
        /**
         * SETUP ENDS
         */
        
        $subscriptionRepository = new SubscriptionRepository($logger, $googleAPIFetch, $googleService);
        $this->assertCount(count($expected_response), $subscriptionRepository->getAllSubscriptions());
    }
}   
