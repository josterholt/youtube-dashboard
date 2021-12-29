<?php

use Google\Service\YouTube;
use josterholt\Repository\YouTubeRepository;
use josterholt\Service\GoogleAPIFetch;
use PHPUnit\Framework\TestCase;

class YouTubeRepositoryTest extends TestCase
{
    /**
     * @covers YouTubeRepository
     */    
    public function testCanCreateYouTubeRepositoryObject()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);

        $mock = $this->getMockBuilder(YouTubeRepository::class)
            ->setConstructorArgs([$logger, $readAdapter, $youTubeAPI])
            ->getMockForAbstractClass();
        $this->assertNotEmpty($mock);
    }

    /**
     * @covers YouTubeRepository
     */    
    public function testWillThrowExceptionIfArgumentDependenciesMissing()
    {
        $this->expectException(TypeError::class);
        $stub = $this->getMockBuilder(YouTubeRepository::class)
            ->setConstructorArgs([null, null])
            ->getMockForAbstractClass();
    }
    
    /**
     * @covers YouTubeRepository
     */    
    public function testCanEnableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);
        
        $mock = $this->getMockForAbstractClass(YouTubeRepository::class, [$logger, $readAdapter, $youTubeAPI]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');
        $mock->disableReadCache(); // Cache is enabled by default

        $mock->enableReadCache();
        $this->assertTrue($mock->getReadCacheState());
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanDisableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);
        
        $mock = $this->getMockForAbstractClass(YouTubeRepository::class, [$logger, $readAdapter, $youTubeAPI]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');

        $mock->disableReadCache();
        $this->assertFalse($mock->getReadCacheState());
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanGetAllItems()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanGetItemById()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanCreateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanUpdateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers YouTubeRepository
     */
    public function testCanDeleteItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }
}
?>
