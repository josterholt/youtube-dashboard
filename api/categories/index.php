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

//echo $_SERVER['REQUEST_URI'] . "\n";
$response = [
    "status" => "SUCCESS",
    "response" => "",
    "error"    => ""
];
if (empty($_GET['categoryID'])) {
    $response["status"] = "FAIL";
    $response["error"] = "Category ID is required";
} elseif (empty($_GET['itemID'])) {
    $response["status"] = "FAIL";
    $response["error"] = "Item ID is required";
}

$category_id = (string) $_GET['categoryID'];
$item_id = (string) $_GET['itemID'];


$redis = getReJSONClient($_ENV['REDIS_URL'], $_ENV['REDIS_PORT'], $_ENV['REDIS_PASSWORD']);

$item_map = $redis->get("categories.items", ".");
if (empty($item_map)) {
    $redis->set("categories.items", ".", ["mapping" => []]);
}

// if (empty($item_map[$category_id])) {
//     $item_map[$category_id] = [];
// }

// if (!in_array($item_id, $item_map[$category_id])) {
//     $item_map[$category_id][] = $item_id;
// }


if (!$redis->arrappend("categories.items", "mapping", ["categoryID" => $category_id, "itemID" => $item_id])) {
    $response["status"] = "FAIL";
    $response["error"] = "Unable to add item to categories";
}


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
