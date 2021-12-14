<?php

use PHPUnit\Framework\TestCase;

class YouTubeRepositoryTest extends TestCase
{
    public function canSetGoogleService()
    {
        $stub = $this->getMockForAbstractClass(YouTubeRepository::class);
        $stub->setGoogleService();
    }

    public function willFailGracefullyWhenServiceIsNotSet()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canEnableCache()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canDisableCache()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canGetAllItems()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canGetItemById()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canCreateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canUpdateItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }

    public function canDeleteItemInDataStore()
    {
        $this->markTestIncomplete("Placeholder");
    }
}
?>