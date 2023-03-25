#!/usr/bin/env bash
docker compose up redis -d
docker compose -f docker-compose.yml run --entrypoint "" web php src/utils/sync_videos.php