<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    use RestCord\DiscordClient;

    #config
    try {
        $config = Yaml::parseFile(__DIR__ . '/../settings.yaml');
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
    };

    $data = json_decode(file_get_contents('php://input'));

    $client = new Predis\Client();
    $redis = new Predis\Client($config["redis-client"]);
    $discord = new DiscordClient($config["discord-client"]);
    function send_msg($embed) {
        //key:    type:card_id:msg_id
        //ext:    5ddaf7385b8585000f203e13:
        if ($redis->get(getallheaders()['x-gk-event'] . ':' . $eventid) == False) {
            $msg = $discord->channel->createMessage(['channel.id' => $config['discord']["channel_id"], 'embed' => $embed]);
            $redis->set(getallheaders()['x-gk-event'] . ':' .  $eventid, $msg['id']);
        } else {
            $discord->channel->editMessage(['channel.id' => $config['discord']["channel_id"], 'message.id' => $redis->get(getallheaders()['x-gk-event'] . ':' . $eventid), 'embed' => $embed]);
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

    //event types https://support.gitkraken.com/developers/webhooks/event-types-payload/#webhook-header
    // Board
    // Column
    // Card 
    // Comment

?>