<?

use Google\Service\YouTube;


require_once("includes/header.php");
$service = new YouTube($client); // Keep this accessible to other functions for reuse.

// Fetch channel subscriptions of authenticated user.
$results = $fetch->get('josterholt.youtube.subscriptions', '.', function ($queryParams) use ($service) {
    $queryParams['mine'] = true;
    return $service->subscriptions->listSubscriptions('contentDetails,snippet', $queryParams);
});

foreach ($results as $result) {
    foreach ($result->items as $item) {
        $subscriptions[] = $item;
    }
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);




$content = "";

$lastActivityLookup = [];
$content = "<div>" . count($subscriptions) . " subscriptions</div>\n";
$content .= "<ul class='formatted-list'>";
foreach ($subscriptions as  $subscription) {

    $results = $fetch->get("josterholt.youtube.channels.{$subscription->snippet->resourceId->channelId}", '.', function ($queryParams) use ($service, $subscription) {
        $queryParams = [
            'id' => $subscription->snippet->resourceId->channelId
        ];
        return $service->channels->listChannels('snippet,contentDetails,statistics', $queryParams);
    });

    $upload_playlist_id = $results[0]->items[0]->contentDetails->relatedPlaylists->uploads;


    $videos = $fetch->get("josterholt.youtube.playlistItems.{$upload_playlist_id}", '.', function ($queryParams) use ($service, $upload_playlist_id) {
        echo $upload_playlist_id . "<br />\n";
        $queryParams = [
            'maxResults' => 25,
            'playlistId' => $upload_playlist_id
        ];

        $results = [];
        try {
            $results = $service->playlistItems->listPlaylistItems('snippet,contentDetails', $queryParams);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return $results;
    });

    $video_html = "";
    if (!empty($videos)) {
        $video_html .= "<div style=\"margin-bottom: 25px;\"><button class='js-video-list-toggle video-toggle' js-channelId='{$subscription->snippet->resourceId->channelId}'>Show Videos</button></div>\n";
        $video_html .= "<ul class='formatted-list' style='display: none' id='js-video-list-{$subscription->snippet->resourceId->channelId}'>";
        foreach ($videos[0]->items as $video) {
            if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($video->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                $lastActivityLookup[$subscription->snippet->resourceId->channelId] = strtotime($video->snippet->publishedAt);
            }

            $formatted_date_str = date('m-d-Y', strtotime($video->snippet->publishedAt));
            $video_html .= "<li><img src='{$video->snippet->thumbnails->default->url}' />" . $video->snippet->title . " (Published: {$formatted_date_str})</li>\n";
        }
        $video_html .= "</ul>";
    }

    $last_activity_str = "Last Activity: ";
    if (isset($lastActivityLookup[$subscription->snippet->resourceId->channelId])) {
        $last_activity_str .= date('m/d/y', $lastActivityLookup[$subscription->snippet->resourceId->channelId]);
    } else {
        $last_activity_str .= "N/A";
    }

    $content .= "<li>
        <img src=\"{$subscription->snippet->thumbnails->default->url}\" /> {$subscription->snippet->title} ({$last_activity_str})
        <div><select name=\"add_category\" js-channel-id=\"{$subscription->snippet->resourceId->channelId}\" js-data-src=\"categories\" style=\"margin-top: 10px; margin-bottom: 5px;\"></select><button name=\"category_submit\">Go</button></div>
    </li>\n";
    $content .= "<!-- {$subscription->snippet->resourceId->channelId} -->\n";

    $content .= $video_html;
}
$content .= "</ul>";

$context = [
    "video_list_content" => $content,
];
echo $twig->render("index.twig", $context);
