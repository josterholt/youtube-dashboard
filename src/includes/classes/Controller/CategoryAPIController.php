<?php

namespace josterholt\Controller;

use Redislabs\Module\ReJSON\ReJSON;
use Psr\Log\LoggerInterface;

class CategoryAPIController
{
    private $_redis = null;
    private $_logger = null;

    /**
     * Accepts a Redis client to use for caching as an argument.
     * 
     * @param LoggerInterface $logger Used for logging.
     * @param  ReJSON $redis Datastore utility.
     */
    public function __construct(LoggerInterface $logger, ReJSON $redis)
    {
        $this->_redis = $redis;
        $this->_logger = $logger;
    }
    public function addItemToCategory()
    {
        $time_pre = microtime(true);

        $response = [
            "status" => "SUCCESS",
            "response" => "",
            "error"    => ""
        ];

        $json = file_get_contents('php://input');
        $data = (array) json_decode($json);

        if (empty($data['category_id'])) {
            $response["status"] = "FAIL";
            $response["error"] = "Category ID is required";
        } elseif (empty($data['item_id'])) {
            $response["status"] = "FAIL";
            $response["error"] = "Item ID is required";
        }

        $category_id = (int) $data['category_id'];
        $category_title = (string) $data['category_title'];
        $item_id = (string) $data['item_id'];


        $this->_logger->debug("[CategoryAPIController] Before Add Category to List: " . microtime(true) - $time_pre . "\n");

        /**
         * ADD CATEGORY TO LIST
         */
        $category_names = $this->_redis->get("categories.names", ".");
        if (empty($category_names)) {
            echo json_encode(["status" => "FAIL", "error" => "No categories found"]);
            die();
        }

        $category_exists = false;
        foreach ($category_names as $category) {
            if ($category->id == $category_id) {
                $category_exists = true;
            }
        }

        if (!$category_exists) {
            echo json_encode(["status" => "FAIL", "error" => "Category does not exist"]);
            die();
        }
        /**
         * END ADD CATEGORY TO LIST
         */

        /**
         * ADD ITEM TO LIST
         */
        $item_map = $this->_redis->get("categories.items", ".");
        if (empty($item_map)) {
            $this->_redis->set("categories.items", ".", ["mapping" => []]);
        }

        if (!$this->_redis->arrappend("categories.items", "mapping", ["categoryID" => $category_id, "itemID" => $item_id])) {
            $response["status"] = "FAIL";
            $response["error"] = "Unable to add item to categories";
        }
        /**
         * END ITEM TO LIST
         */


        $mapping = $this->_redis->getArray("categories.items", ".mapping");


        $this->_logger->debug("[CategoryAPIController] Mapping items... " . microtime(true) - $time_pre . "\n");

        $mapped_items = [];
        foreach ($mapping as $map_item) {
            $cat_id = $map_item["categoryID"];
            $item_id = $map_item["itemID"];
            if (!isset($mapped_items[$cat_id])) {
                $mapped_items[$cat_id] = [];
            }
            if (!in_array($item_id, $mapped_items[$cat_id])) {
                $mapped_items[$cat_id][] = $item_id;
            }
        }

        $response["data"] = $mapped_items;
        echo json_encode($response);
    }
}
