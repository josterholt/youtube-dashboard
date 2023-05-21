<?php

use Google\Service\YouTube;
use PHPUnit\Framework\TestCase;
use josterholt\Repository\SubscriptionRepository;
use josterholt\Service\GoogleAPIFetch;
use Redislabs\Module\ReJSON\ReJSON;
use Google\Service\YouTube\Resource\Subscriptions;
use Google\Service\YouTube\SubscriptionListResponse;

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


        /** @var Psr\Log\LoggerInterface $logger */
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $youTubeAPI = $this->createStub(YouTube::class);


        $redisStore = $this->createStub('josterholt\Service\Storage\RedisStore');
        $redisStore->method('get')->willReturn($test_subscriptions);
        /**
         * SETUP ENDS
         */

        $subscriptionRepository = new SubscriptionRepository($logger, $redisStore, $youTubeAPI);
        $subscription_results = $subscriptionRepository->getSubscriptionsFromAPI();

        $this->assertCount(count($test_subscriptions), $subscription_results);
    }

    /**
     * Help function to create a subscription list response with
     * next token.
     * 
     * @param array  $items Array of subscriptions [ "id" => NUM ]
     * @param string $nextPageToken Token value, null if last page
     * 
     * @return SubscriptionListResponse $subscriptionListResponse
     */
    public function generateSubscriptionListResponse(
        array $items,
        string $nextPageToken = null
    ): SubscriptionListResponse {
        $subscriptionListResponse = new SubscriptionListResponse(
            [
                "items" => $items,
            ]
        );

        $subscriptionListResponse->setNextPageToken($nextPageToken);
        return $subscriptionListResponse;
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
        $api_response[] = $this->generateSubscriptionListResponse(
            [
                ["id" => 1],
                ["id" => 2],
            ],
            "pageToken1"
        );

        $api_response[] = $this->generateSubscriptionListResponse(
            [
                ["id" => 3],
                ["id" => 4],
            ],
            "pageToken2"
        );

        $api_response[] = $this->generateSubscriptionListResponse(
            [
                ["id" => 5],
                ["id" => 6],
            ]
        );

        $expected_response = [
            ["id" => 1],
            ["id" => 2],
            ["id" => 3],
            ["id" => 4],
            ["id" => 5],
            ["id" => 6],
        ];


        /** @var Psr\Log\LoggerInterface $logger  */
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $youTubeAPI = $this->createStub(YouTube::class);


        $subscriptionsMock = $this->createStub(Subscriptions::class);
        $subscriptionsMock->method('listSubscriptions')
            ->willReturnOnConsecutiveCalls(
                $api_response[0],
                $api_response[1],
                $api_response[2]
            );

        $youTubeAPI->subscriptions = $subscriptionsMock;

        $redisStore = $this->createStub('josterholt\Service\Storage\RedisStore');
        $redisStore->method('get')->willReturn($api_response);
        /**
         * SETUP ENDS
         */

        $subscriptionRepository = new SubscriptionRepository(
            $logger,
            $redisStore,
            $youTubeAPI
        );
        $this->assertCount(
            count($expected_response),
            $subscriptionRepository->getAllSubscriptions()
        );
    }
}
