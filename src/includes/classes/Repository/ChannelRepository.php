<?php

namespace josterholt\Repository;

class ChannelRepository extends AbstractYouTubeRepository
{
    /**
     * Fetch multiple channels by subscription ID in batched API calls.
     * Per-ID cache checks are performed first; only uncached IDs hit the API.
     * Returns a map of channelId => channel object.
     */
    public function getBySubscriptionIds(array $subscription_ids): array
    {
        // @todo Simplify this function. Refactor this method so that caching and response fetching is encapsulated in a different class.
        $channels_by_id = [];
        $uncached_ids = [];

        foreach ($subscription_ids as $id) {
            $cached = $this->service->getCached("youtube.channels.{$id}");
            if (!empty($cached)) {
                $this->logger->debug("Using cache for channel {$id}.");
                foreach ($cached as $response) {
                    foreach ($response->items ?? [] as $channel) {
                        $channels_by_id[$channel->id] = $channel;
                    }
                }
            } else {
                $uncached_ids[] = $id;
            }
        }

        foreach (array_chunk($uncached_ids, 50) as $chunk) {
            $this->logger->debug("Fetching " . count($chunk) . " channels from API.");
            $responses = $this->service->getAllGoogleServiceResponses(function ($queryParams) use ($chunk) {
                return $this->service->channels->listChannels(
                    'snippet,contentDetails,statistics,contentOwnerDetails',
                    array_merge($queryParams, ['id' => implode(',', $chunk)])
                );
            });

            foreach ($responses as $response) {
                foreach ($response->items ?? [] as $channel) {
                    $channels_by_id[$channel->id] = $channel;
                    $this->service->setCached(
                        "youtube.channels.{$channel->id}",
                        [(object)['items' => [$channel]]]
                    );
                }
            }
        }

        return $channels_by_id;
    }

    public function getAll(): array
    {
        return [];
    }

    public function getById($id): object|null
    {
        return null;
    }

    public function create(object $record): bool
    {
        return false;
    }

    public function update(object $record): bool
    {
        return false;
    }

    public function delete($id): bool
    {
        return false;
    }
}
