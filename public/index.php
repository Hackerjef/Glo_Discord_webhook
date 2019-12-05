<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    use RestCord\DiscordClient;
    use RedisClient\RedisClient;
    use RedisClient\Client\Version\RedisClient2x6;
    use RedisClient\ClientFactory;

    #config
    try {
        $config = Yaml::parseFile(__DIR__ . '/../settings.yaml');
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
    }

    $Redis = ClientFactory::create($config["redis"]);
    $discord = new DiscordClient(['token' => $config["discord"]["token"]]);
    # Figure out what to do next lol
    //var_dump($discord->channel->createMessage(['channel.id' => 521181423512846357, 'content' => 'This is a test.jpg']));

    class event_router {
        public function boards() {
            echo "boards";
            exit;
        }

        public function columns() {
            echo "columns";
            exit;
        }

        public function cards() {
            echo "cards";
            exit;
        }

        public function comments() {
            echo "comments";
            exit;
        }

        public function invalid() {
            header("HTTP/1.0 404 Invalid event");
            exit;
        }
    }

    # only allow post requests
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header("HTTP/1.0 405 Method Not Allowed");
        exit;
    }

    # only allow application/json content-type
    if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
        header("HTTP/1.0 400 Invalid Content type");
        exit;
    }

    #Check header exists, if so check hash
    $hash = "sha1=" . hash_hmac('sha1', file_get_contents('php://input'), $config["glo"]["secret"]);
    if ($config["glo"]["check_secret"] and $hash != getallheaders()['x-gk-signature']) {
        header("HTTP/1.0 403 Invalid secret");
        exit;
    }

    if (method_exists('event_router', getallheaders()['x-gk-event'])) {
        [new event_router(), getallheaders()['x-gk-event']]();
    } else {
        [new event_router(), "invalid"]();
    }
?>