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

    $edit = 654825520172761098;
    //$msg = $discord->channel->createMessage(['channel.id' => $config['discord']["channel_id"], 'content' => "fuck"]);
    $msg = $discord->channel->editMessage(['channel.id' => $config['discord']["channel_id"], 'message.id' => $edit, 'content' => "shit"]);
    echo $msg;
    exit;
?>