<?php
    date_default_timezone_set("America/New_York");
    require_once __DIR__ . '/../vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    use RestCord\DiscordClient;


    # dont even start unless its a post request
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        header("HTTP/1.0 405 Method Not Allowed");
        exit;
    }

    # only allow application/json content-type
    if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
        header("HTTP/1.0 400 Invalid Content type");
        exit;
    }

    # if event is not card, return 200 ok!
    if (getallheaders()['x-gk-event'] != "cards") {
        header("HTTP/1.0 200 Ignoring Event but OK!");
        exit;
    }

    # config
    try {
        $config = Yaml::parseFile(__DIR__ . '/../settings.yaml');
    } catch (ParseException $exception) {
        printf('Unable to parse YAML %s', $exception->getMessage());
    };

    # Check header exists, if so check hash
    $hash = "sha1=" . hash_hmac('sha1', file_get_contents('php://input'), $config["glo"]["secret"]);
    if ($config["glo"]["check_secret"] and $hash != getallheaders()['x-gk-signature']) {
        header("HTTP/1.0 403 Invalid secret");
        exit;
    }

    $data = json_decode(file_get_contents('php://input'));
    $redis = new Predis\Client($config["redis-client"]);
    $discord = new DiscordClient($config["discord-client"]);


    function send_msg($embed) {
        //key:    card_id:msg_id
        if ($redis->get($data["card"]["id"]) == False) {
            $msg = $discord->channel->createMessage(['channel.id' => $config['discord']["channel_id"], 'embed' => $embed]);
            $redis->set($data["card"]["id"], $msg['id']);
        } else {
            if ($redis->get($data["card"]["id"]) == "Null") {
                header("HTTP/1.0 200 origional message deleted");
                exit;
            }
            $discord->channel->editMessage(['channel.id' => $config['discord']["channel_id"], 'message.id' => $redis->get($data["card"]["id"]), 'embed' => $embed]);
        }
    }

    function update_topic() {
        $discord->channel->modifyChannel(['channel.id' => $config['discord']["channel_id"], 'topic' => "Current workflow For Aperture. **Last updated**:" . date(" d/m/y h:ia ") . "EST"]);
    }

    if ($data["action"] === "deleted") {
        if ($redis->get($data["card"]["id"]) != False) {
            $discord->channel->deleteMessage(['channel.id' => $config['discord']["channel_id"], 'message.id' => $redis->get($data["card"]["id"])]);
            $redis->set($data["card"]["id"], "Null");
        }
    }

    //create
    //deleted
    //update
    //moved_column
?>