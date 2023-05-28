<?php

namespace josterholt\Repository;

class ChannelRepository extends AbstractYouTubeRepository
{
    public function getBySubscriptionId(string $subscription_id): array
    {
        try {
            // TODO: Is there a way to pull channels in bulk?
            // TODO: This should throw an informative exception if readAdapter is not set.
            $channels = $this->service->queryFromCache(
                "youtube.channels.{$subscription_id}",
                function () use ($subscription_id) {
                    return $this->service->channels->listChannels('snippet,contentDetails,statistics,contentOwnerDetails', ['id' => $subscription_id]);
                }
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $channels;
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
