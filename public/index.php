<?php
    require_once __DIR__ . '/../vendor/autoload.php';
    use Symfony\Component\Yaml\Yaml;
    use \DiscordWebhooks\Client;
    use \DiscordWebhooks\Embed;

    function($config, $embed) {
        $webhook = new Client($config["discord"]["url"]);
        $webhook->username($config["discord"]["username"])->avatar($config['webserver']['target_server'] . "/src/" . $config['discord']['avatar_filename'])->embed($embed)->send();
    };


    try {
        $config = Yaml::parseFile(__DIR__ . '/../settings.yaml');
    } catch (ParseException $exception) {
        printf('Unable to parse the YAML string: %s', $exception->getMessage());
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
?>