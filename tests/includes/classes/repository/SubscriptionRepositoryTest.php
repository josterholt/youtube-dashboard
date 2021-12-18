<?php

use Google\Collection;
use PHPUnit\Framework\TestCase;
use josterholt\Repository\SubscriptionRepository;
use josterholt\Service\GoogleAPIFetch;
use josterholt\Service\RedisService;
use Redislabs\Module\ReJSON\ReJSON;

class SubscriptionRepositoryTest extends TestCase {
    public function testCanGetAll() {
        $mockCollection = $this->createMock(Collection::class);
        $mockCollection->items = [
            ["Testing"]
        ];
        
        
        $test_subscriptions = [
                $mockCollection,
                $mockCollection,
        ];

        
        $mockSubscriptionRepository = $this->getMockBuilder(SubscriptionRepository::class)
        ->disableOriginalConstructor()
        ->onlyMethods(["_getReadAdapter"])
        ->getMock();
        
        $mockGoogleAPIFetch = $this->createMock(GoogleAPIFetch::class);
        $mockGoogleAPIFetch->method('get')
        ->willReturn($test_subscriptions);

        $mockSubscriptionRepository->expects($this->any())
        ->method('_getReadAdapter')
        ->willReturn($this->returnValue($mockGoogleAPIFetch));

        // Initial object with mocked GoogleAPIFetch and RedisJSON
        $mockSubscriptionRepository->__construct();
        
        $this->assertCount(count($test_subscriptions), $mockSubscriptionRepository->getAll());


    }
}   