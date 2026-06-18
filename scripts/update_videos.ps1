docker compose up redis -d
docker compose -f docker-compose.yml run -p 8089:8089 --entrypoint "" web php src/utils/sync_videos.php