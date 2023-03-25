## Setup

1. Follow the [PHP Quickstart](https://developers.google.com/youtube/v3/quickstart/php) guide to acquire client secret.
2. Store client secret in a file accessible by this app.
3. Create .env file at root folder of this app.
   a. Use the template below, replace values as needed.


### Populating Subscriptions and Videos

```
# From host
docker exec [container name] php /var/www/html/src/utils/sync_videos.php

# From container
php ./src/utils/sync_videos.php
```

### .env

ACCESS_TOKEN_FILE_PATH=./access_token.json
REDIS_PASSWORD=
REDIS_URL=redis
REDIS_PORT=6379

## Docker

This codebase has a Dockerfile and docker-compose.yml for development purposes.

**Note:** The PHP container will run run_dev_server.sh when it starts. This shell script installs Composer dependencies and starts PHP's built-in developer server.

### Commands

```
docker-compose up # Spins PHP and Redis-JSON containers
docker-compose down # Tears down containers

# Opens a shell for a running container
docker exec -it [container name] bash

# Access Redis-CLI (starting from host)
docker exec -it [container name] bash
redis-cli
```

## Add Categories
```
# Start redis-cli client
JSON.ARRAPPEND categories.names . '{ "id": [unique id], "title": "Name of category" }'
```

## Dependencies

-   [Google APIs Client](https://github.com/googleapis/google-api-php-client)
-   [Redis Json](https://redis.com/blog/redis-as-a-json-store/)
-   [Redis-JSON PHP](https://github.com/mkorkmaz/redislabs-rejson)
-   [PHP dotenv](https://github.com/vlucas/phpdotenv)

### Optional

-   [Docker](https://hub.docker.com/)

## API

# Response Structure

**Success**

```
{
   "status": "SUCCESS",
   "response": "",
   "error": ""
}
```

**Failure**

```
{
   "status": "FAIL",
   "response": "",
   "error": ""
}
```

# Subsystems

## Categorization

/api/categories is intended as a generic categorization mechanism. All it needs is an item and category.

### Data Structure (Stored in Redis

)

```
// categories._id
int

// categories.names
[
   {
      id: int (autoincrement),
      title: string
   },
]

// categories.items
{
   mapping: [{
      categoryID: int,
      itemID: string (hash)
   }]
}
```
