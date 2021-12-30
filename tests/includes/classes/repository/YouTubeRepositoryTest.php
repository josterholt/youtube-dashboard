<?php

use Google\Service\YouTube;
use josterholt\Repository\AbstractYouTubeRepository;
use josterholt\Service\GoogleAPIFetch;
use PHPUnit\Framework\TestCase;

class AbstractYouTubeRepositoryTest extends TestCase
{
    /**
     * @covers AbstractYouTubeRepository
     */    
    public function testCanCreateAbstractYouTubeRepositoryObject()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);

        $mock = $this->getMockBuilder(AbstractYouTubeRepository::class)
            ->setConstructorArgs([$logger, $readAdapter, $youTubeAPI])
            ->getMockForAbstractClass();
        $this->assertNotEmpty($mock);
    }

    /**
     * @covers AbstractYouTubeRepository
     */    
    public function testWillThrowExceptionIfArgumentDependenciesMissing()
    {
        $this->expectException(TypeError::class);
        $stub = $this->getMockBuilder(AbstractYouTubeRepository::class)
            ->setConstructorArgs([null, null])
            ->getMockForAbstractClass();
    }
    
    /**
     * @covers AbstractYouTubeRepository
     */    
    public function testCanEnableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();        
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);
        
        $mock = $this->getMockForAbstractClass(AbstractYouTubeRepository::class, [$logger, $readAdapter, $youTubeAPI]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');
        $mock->disableReadCache(); // Cache is enabled by default

        $mock->enableReadCache();
        $this->assertTrue($mock->getReadCacheState());
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanDisableCache()
    {
        $logger = $this->getMockBuilder(Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();
        $readAdapter = $this->createStub(GoogleAPIFetch::class);
        $youTubeAPI = $this->createStub(YouTube::class);
        
        $mock = $this->getMockForAbstractClass(AbstractYouTubeRepository::class, [$logger, $readAdapter, $youTubeAPI]);
        $this->assertTrue($mock->getReadCacheState(), 'Cache should be enabled by default');

        $mock->disableReadCache();
        $this->assertFalse($mock->getReadCacheState());
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanGetAllItems()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanGetItemById()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanCreateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanUpdateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    /**
     * @covers AbstractYouTubeRepository
     */
    public function testCanDeleteItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }
}
?>
