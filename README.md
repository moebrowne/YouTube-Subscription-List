# YouTube Sub List

A simple web app which aggregates videos from YouTube channels via RSS, no account needed.

![Screenshot](screenshot.png)



## Run

The easiest way to get started is to run PHPs built-in webserver. PHP 8.4 is required.

```
PHP_CLI_SERVER_WORKERS=$(nproc) php -S localhost:8008 -t public
```

There is also a Docker container:

```
docker build -t youtube-subscriptions .
docker run --name youtube-subscriptions -e PHP_CLI_SERVER_WORKERS=$(nproc) -d -p 80:8008 youtube-subscriptions
```


# Channel Subscriptions 

The list of subscribed channels is defined in a `channels.json` file in the root directory. It should look like this:

```json
{
    "<CHANNEL_ID1>": {},
    "<CHANNEL_ID2>": {
        "featured": true
    }
}
```
