<?php
namespace josterholt\Controller;

use josterholt\Repository\CategoryNameRepository;
use josterholt\Repository\CategoryItemRepository;
use josterholt\Repository\PlayListItemRepository;
use josterholt\Repository\SubscriptionRepository;
use josterholt\Repository\ChannelRepository;

class YouTubeDashboardController
{
    /**
     * @Inject
     * @var    CategoryNameRepository
     */
    private $_categoryNameRepository;

    /**
     * @Inject
     * @var    CategoryItemRepository
     */
    private $_categoryItemRepository;    

    /**
     * @Inject
     * @var    SubscriptionRepository
     */
    private $_subscriptionRepository;

    /**
     * @Inject
     * @var    ChannelRepository
     */
    private $_channelRepository;

    /**
     * @Inject
     * @var    PlaylistItemRepository
     */
    private $_playListItemRepository;

    private $_subscriptions = null;

    protected function itemCategoryLookup()
    {
        $data = $this->_categoryNameRepository->getAll();

        $category_title_lookup = [];
        foreach($data as $category) {
            $category_title_lookup[$category->id]['categoryTitle'] = $category->title;
        }

        $item_category_lookup = [];
        $data = $this->_categoryItemRepository->getAll();
        if (!empty($data)) {
            foreach ($data['mapping'] as $map) {
                if (empty($map['itemID'])) {
                    continue;
                }

                $category_title = "None";
                if (isset($category_title_lookup[$map['categoryID']])) {
                    $category_title = $category_title_lookup[$map['categoryID']]['categoryTitle'];
                }
                $item_category_lookup[$map['itemID']] = ["categoryID" => $map['categoryID'], "categoryTitle" => $category_title];
            }
        }
        return $item_category_lookup;
    }

    protected function getLastActivityLookup()
    {
        $lastActivityLookup = []; // Store last video upload activity for display
        foreach ($this->_subscriptions as  $subscription) {           
            // @todo is there a way to pull channels in bulk?    
            $channels = $this->_channelRepository->getBySubscriptionId($subscription->snippet->resourceId->channelId);

            if(empty($channels)) {
                continue;
            }

            $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
            $play_list_items = $this->_playListItemRepository->getByPlaylistId($upload_playlist_id);
            if(!empty($play_list_items)) {
                foreach ($play_list_items[0]->items as $play_list_item) {            
                    if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($play_list_item->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                        $lastActivityLookup[$subscription->snippet->resourceId->channelId] = strtotime($play_list_item->snippet->publishedAt);
                    }
                }
            }
        }
        return $lastActivityLookup;
    }

    protected function getChannelsLookup()
    {
        $channels_lookup = [];
        foreach ($this->_subscriptions as  $subscription) {           
            // @todo is there a way to pull channels in bulk?    
            $channels = $this->_channelRepository->getBySubscriptionId($subscription->snippet->resourceId->channelId);

            if(empty($channels)) {
                continue;
            }

            $channels_lookup[$subscription->snippet->resourceId->channelId] = $channels[0]->items[0];
        }
        return $channels_lookup;
    }

    protected function getPlayListItemsLookup()
    {
        $play_list_items_lookup = [];
        foreach ($this->_subscriptions as  $subscription) {           
            // @todo is there a way to pull channels in bulk?    
            $channels = $this->_channelRepository->getBySubscriptionId($subscription->snippet->resourceId->channelId);

            if(empty($channels)) {
                continue;
            }

            $channels_lookup[$subscription->snippet->resourceId->channelId] = $channels[0]->items[0];


            $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
            $play_list_items = $this->_playListItemRepository->getByPlaylistId($upload_playlist_id);
            if(!empty($play_list_items)) {
                foreach ($play_list_items[0]->items as $play_list_item) {            
                    if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($play_list_item->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                        $lastActivityLookup[$subscription->snippet->resourceId->channelId] = strtotime($play_list_item->snippet->publishedAt);
                    }
                }
            }

            $play_list_items_lookup[$subscription->snippet->resourceId->channelId] = $play_list_items;
        }
        return $play_list_items_lookup;
    }

    protected function getGroupedChannelsByCategory()
    {
        $item_category_lookup = $this->itemCategoryLookup();
        $play_list_items_lookup = $this->getPlayListItemsLookup();
        $channels_lookup = $this->getChannelsLookup();
        $lastActivityLookup = $this->getLastActivityLookup();



        $selected_category = "";
        if(!empty($_GET['category']) && $_GET['category'] != 'NO_FILTER') {
            $selected_category = $_GET['category'];
        }

        $grouped_channel_sets = [];
        foreach ($this->_subscriptions as  $subscription) {
            $displayed_channels[] = $subscription->snippet->resourceId->channelId;
            $play_list_items = $play_list_items_lookup[$subscription->snippet->resourceId->channelId];

            $last_activity = "Last Activity: ";
            if (isset($lastActivityLookup[$subscription->snippet->resourceId->channelId])) {
                $last_activity .= date('m/d/y', $lastActivityLookup[$subscription->snippet->resourceId->channelId]);
            } else {
                $last_activity .= "N/A";
            }

            if(!isset($item_category_lookup[MD5($subscription->snippet->resourceId->channelId)])) {
                $category = ["categoryID" => 0, "categoryTitle" => "None"];
            } else {
                $category = $item_category_lookup[MD5($subscription->snippet->resourceId->channelId)];
            }

            if(empty($selected_category) || $category['categoryID'] == $selected_category) {
                $grouped_channel_sets[$category['categoryID']]['category'] = $category;
                $channel = $channels_lookup[$subscription->snippet->resourceId->channelId];
                $grouped_channel_sets[$category['categoryID']]['items'][] = ["subscription" => $subscription, "channel" => $channel, "play_list_items" => $play_list_items, "last_activity" => $last_activity];
            }
        }

        usort(
            $grouped_channel_sets, function ($set_a, $set_b) {   
                if($set_a['category']['categoryID'] == 0) {
                    return 1;
                }

                if($set_b['category']['categoryID'] == 0) {
                    return -1;
                }

                return strnatcmp($set_a['category']['categoryTitle'], $set_b['category']['categoryTitle']);
            }
        );

        return $grouped_channel_sets;
    }

    public function videoListing()
    {
        $this->_subscriptions = $this->_subscriptionRepository->getAllSubscriptions();

        $context = [
            "grouped_channel_sets" => $this->getGroupedChannelsByCategory(),
        ];

        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $twig = new \Twig\Environment($loader);
        echo $twig->render("index.twig", $context);
    }
}
