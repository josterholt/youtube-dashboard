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

    protected function itemCategoryLookup(): array
    {
        $data = $this->_categoryNameRepository->getAll();

        $category_title_lookup = [];
        foreach ($data as $category) {
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
            $channels = $this->_channelRepository->getBySubscriptionId(
                $subscription->snippet->resourceId->channelId
            );

            if (empty($channels)) {
                continue;
            }

            $upload_playlist_id = $channels[0]->items[0]->contentDetails->relatedPlaylists->uploads;
            $play_list_items = $this->_playListItemRepository->getByPlaylistId(
                $upload_playlist_id
            );
            if (!empty($play_list_items)) {
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
            $channels = $this->_channelRepository->getBySubscriptionId(
                $subscription->snippet->resourceId->channelId
            );

            if (empty($channels)) {
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
            $channels = $this->_channelRepository->getBySubscriptionId(
                $subscription->snippet->resourceId->channelId
            );

            if (empty($channels)) {
                continue;
            }

            $channels_lookup[$subscription->snippet->resourceId->channelId] = $channels[0]->items[0];


            $upload_playlist_id = $channels[0]->items[0]->contentDetails
                ->relatedPlaylists->uploads;
            $play_list_items = $this->_playListItemRepository
                ->getByPlaylistId($upload_playlist_id);
            if (!empty($play_list_items)) {
                foreach ($play_list_items[0]->items as $play_list_item) {
                    if (!isset($lastActivityLookup[$subscription->snippet->resourceId->channelId]) || strtotime($play_list_item->snippet->publishedAt) > $lastActivityLookup[$subscription->snippet->resourceId->channelId]) {
                        $lastActivityLookup[$subscription->snippet->resourceId
                            ->channelId] = strtotime(
                            $play_list_item
                                ->snippet->publishedAt
                        );
                    }
                }
            }

            $play_list_items_lookup[$subscription->snippet->resourceId
                ->channelId] = $play_list_items;
        }
        return $play_list_items_lookup;
    }

    protected function getGroupedChannelsByCategory()
    {
        $NUM_VIDEOS_DISPLAYED = 5;

        $item_category_lookup = $this->itemCategoryLookup();
        $play_list_items_lookup = $this->getPlayListItemsLookup();
        $channels_lookup = $this->getChannelsLookup();
        $lastActivityLookup = $this->getLastActivityLookup();



        $selected_category = "";
        if (!empty($_GET['category']) && $_GET['category'] != 'NO_FILTER') {
            $selected_category = $_GET['category'];
        }

        $grouped_channel_sets = [];
        foreach ($this->_subscriptions as  $subscription) {
            $displayed_channels[] = $subscription->snippet->resourceId->channelId;
            $play_list_items = $play_list_items_lookup[$subscription->snippet->resourceId->channelId];

            if (count($play_list_items) > 0) {
                $play_list_items[0]->items = array_slice($play_list_items[0]->items, 0, $NUM_VIDEOS_DISPLAYED);
            }

            $last_activity = "Updated: ";
            if (isset($lastActivityLookup[$subscription->snippet->resourceId->channelId])) {
                $last_activity .= date('m/d/y', $lastActivityLookup[$subscription->snippet->resourceId->channelId]);
            } else {
                $last_activity .= "N/A";
            }

            if (!isset($item_category_lookup[MD5($subscription->snippet->resourceId->channelId)])) {
                $category = ["categoryID" => 0, "categoryTitle" => "None"];
            } else {
                $category = $item_category_lookup[MD5($subscription->snippet->resourceId->channelId)];
            }

            if (
                empty($selected_category)
                || $category['categoryID'] == $selected_category
                || ($selected_category == "UNCATEGORIZED" && $category['categoryID'] == 0)
            ) {
                $grouped_channel_sets[$category['categoryID']]['category'] = $category;
                $grouped_channel_sets[$category['categoryID']]['items'][] = [
                    "subscription" => $subscription,
                    "channel" => $channels_lookup[$subscription->snippet->resourceId->channelId],
                    "play_list_items" => $play_list_items,
                    "last_activity" => $last_activity
                ];
            }
        }

        usort(
            $grouped_channel_sets,
            function ($set_a, $set_b) {
                if ($set_a['category']['categoryID'] == 0) {
                    return 1;
                }

                if ($set_b['category']['categoryID'] == 0) {
                    return -1;
                }

                return strnatcmp($set_a['category']['categoryTitle'], $set_b['category']['categoryTitle']);
            }
        );

        return $grouped_channel_sets;
    }

    private function _paginateCategorizedSubscriptions(array $categorized_channels, int $limit, int $offset)
    {
        $paginated_list = [];
        $total_count = 0;
        $category_offset = $offset;

        for ($i = 0; $i <= count($categorized_channels); $i++) {
            // @todo look into why there are missing indexes
            if (!isset($categorized_channels[$i])) {
                continue;
            }

            $category = $categorized_channels[$i];
            $paginated_item_list = array_slice($category['items'], $category_offset);

            if ($total_count + count($paginated_item_list) > $limit) {
                $diff_count = $limit - ($total_count + count($paginated_item_list));
                $paginated_item_list = array_slice($paginated_item_list, 0, $diff_count);
            }

            $category_offset = max($category_offset - count($category['items']), 0);

            if (count($paginated_item_list) > 0) {
                $paginated_list[] = array_replace($category, ["items" => $paginated_item_list]);
            }

            $total_count += count($paginated_item_list);

            if ($total_count == $limit) {
                break;
            }
        }

        return $paginated_list;
    }

    public function videoListing()
    {
        $limit  = 10;
        $offset = 0;
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }

        $current_page = 1;
        if ($offset > 0) {
            $current_page = max($limit / $offset, 1);
        }

        $this->_subscriptions = $this->_subscriptionRepository->getAllSubscriptions();
        $subscription_count = count($this->_subscriptions);

        $context = [
            "grouped_channel_sets" => $this->_paginateCategorizedSubscriptions($this->getGroupedChannelsByCategory(), $limit, $offset),
            "pagination" => [
                "num_pages"    =>  ceil($subscription_count / $limit),
                "current_page" => $current_page,
                "limit"        => $limit
            ]
        ];

        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $twig = new \Twig\Environment($loader);
        echo $twig->render("index.twig", $context);
    }
}
