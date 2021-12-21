<?

/**
 * BEGIN AUTOLOAD SCRIPTS
 */
if (!file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
}
require_once __DIR__ . '/../../vendor/autoload.php';
/**
 * END AUTOLOAD SCRIPTS
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

require_once("../../includes/functions.php");

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


$redis = RedisService::getInstance();

/**
 * ADD CATEGORY TO LIST
 */
$category_names = $redis->get("categories.names", ".");
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
$item_map = $redis->get("categories.items", ".");
if (empty($item_map)) {
    $redis->set("categories.items", ".", ["mapping" => []]);
}

if (!$redis->arrappend("categories.items", "mapping", ["categoryID" => $category_id, "itemID" => $item_id])) {
    $response["status"] = "FAIL";
    $response["error"] = "Unable to add item to categories";
}
/**
 * END ITEM TO LIST
 */


$mapping = $redis->getArray("categories.items", ".mapping");


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
