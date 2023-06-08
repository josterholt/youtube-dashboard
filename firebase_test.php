<?php
require 'vendor/autoload.php';

use josterholt\Service\Storage\FireStore;
use Google\Cloud\Firestore\FirestoreClient;
// use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Factory;

$firestore = new FirestoreClient([
    'keyFile' => json_decode(file_get_contents('./secrets/youtube-dashboard-325222-firebase-adminsdk-77t7j-b446e44ddf.json'), true)
]);

/*
$firestore = $factory = (new Factory)
    ->withServiceAccount('secrets/youtube-dashboard-325222-firebase-adminsdk-77t7j-b446e44ddf.json')
    ->createFirestore()->database();
*/
$documentReference = $firestore->collection("test_docs")->document(3);
print_r($documentReference);
/*
$documentReference->set([
    "title" => "Foo"
]);
*/
/*
$test = new FireStore($firestore);
$test->set("test_key", "test value");
*/
