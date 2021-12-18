<?php

use PHPUnit\Framework\TestCase;
use josterholt\Service\GoogleAPIFetch;

class GoogleAPIFetchTest extends TestCase {
    public function testCanCallGet() {
        $cacheInstance = $this->createMock('\Redislabs\Module\ReJSON\ReJSON');
        $cacheInstance->expects($this->once())->method("get");
        $cacheInstance->expects($this->once())->method("set");

        $callback = $this->getMockBuilder(\stdClass::class)
        ->addMethods(['__invoke'])
        ->getMock();
        $callback->expects($this->once())->method('__invoke');
       


        $fetch = new GoogleAPIFetch($cacheInstance);
        $fetch->get("test.namespace", '.', $callback);
    }
}   