<?php

use PHPUnit\Framework\TestCase;
use Google\Service\YouTube;
use josterholt\Repository\PlayListItemRepository;
use josterholt\Service\GoogleAPIFetch;
use Redislabs\Module\ReJSON\ReJSON;


class PlayListItemRepositoryTest extends TestCase
{

    /**
     * @covers PlayListItemRepository
     */
    public function testGetByPlayListId()
    {
        $videoList = [1,2,3];

        // DEPENDENCY SETUP BEGIN
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        
        $redis = $this->createStub(ReJSON::class);
        $youTubeAPI = $this->createStub(YouTube::class);

        $googleAPIFetch = $this->getMockBuilder(GoogleAPIFetch::class)
            ->setConstructorArgs([$logger, $redis])
            ->getMock();

        $googleAPIFetch->method('get')
            ->willReturn($videoList);
        // DEPENDENCY SETUP END

        $playListItemRepository = new PlayListItemRepository($logger, $googleAPIFetch, $youTubeAPI);
        $this->assertEquals($videoList, $playListItemRepository->getByPlayListId(1));

    }
}
