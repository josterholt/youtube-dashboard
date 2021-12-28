<?php

use PHPUnit\Framework\TestCase;
use Redislabs\Module\ReJSON\ReJSON;
use josterholt\Repository\CategoryNameRepository;

/**
 * Category names are stored in REDIS (w/ ReJSON) as an JSON array.
 * [
 *    {
 *      "id": 1,
 *      "title": "Category Name"
 *    }
 * ]
 */
class CategoryNameRepositoryTest extends TestCase
{
    /**
     * @covers CategoryNameRepository
     */
    public function testGetAll()
    {
        $categoryNames = [
            [
                "id"    => 1000,
                "title" => "Music",
            ],
            [
                "id"    => 1010,
                "title" => "Science & Technology"
            ],
            [
                "id"    => 1005,
                "title" => "Games",
            ]
        ];

        $reJSONClient = $this->createStub(ReJSON::class);
        $reJSONClient->method("get")
            ->willReturn($categoryNames);

        $categoryNameRepo = new CategoryNameRepository($reJSONClient);
        $names = $categoryNameRepo->getAll();
        
        $this->assertEquals($categoryNames, $names);

    }

    /**
     * @covers CategoryNameRepository
     */
    public function testGetById()
    {
        $categoryNames = [
            [
                "id"    => 1000,
                "title" => "Music",
            ],
            [
                "id"    => 1010,
                "title" => "Science & Technology"
            ],
            [
                "id"    => 1005,
                "title" => "Games",
            ]
        ];

        $reJSONClient = $this->createStub(ReJSON::class);
        $reJSONClient->method("get")
            ->willReturn($categoryNames);

        $categoryNameRepo = new CategoryNameRepository($reJSONClient);
        $name = $categoryNameRepo->getById($categoryNames[1]["id"]);
        
        $this->assertNotEmpty($name);
        $this->assertIsArray($name);
        $this->assertEquals($categoryNames[1]["id"], $name["id"]);
    }

    /**
     * @covers CategoryNameRepository
     */
    public function create()
    {
        $this->markTestIncomplete("Create test not implemented yet.");
    }
}
