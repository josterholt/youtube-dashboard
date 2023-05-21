<?php

use PHPUnit\Framework\TestCase;
use Google\Service\YouTube;
use josterholt\Repository\PlayListItemRepository;
use josterholt\Service\Storage\RedisStore;
use Redislabs\Module\ReJSON\ReJSON;


class PlayListItemRepositoryTest extends TestCase
{

    /**
     * @covers PlayListItemRepository
     */
    public function testGetByPlayListId()
    {
        $videoList = [1, 2, 3];

        // DEPENDENCY SETUP BEGIN
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $redis = $this->createStub(ReJSON::class);

        $store = $this->getMockBuilder(RedisStore::class)
            ->setConstructorArgs([$logger, $redis])
            ->getMock();

        $store->method('get')
            ->willReturn($videoList);

        $youTubeAPI = $this->createStub(YouTube::class);
        // DEPENDENCY SETUP END

        $playListItemRepository = new PlayListItemRepository($logger, $store, $youTubeAPI);
        $this->assertEquals($videoList, $playListItemRepository->getByPlayListId(1));
    }
}
