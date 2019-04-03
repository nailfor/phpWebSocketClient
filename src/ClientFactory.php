<?php

namespace nailfor\websocket;

use React\EventLoop\Factory;

class ClientFactory
{
    public static $client;
    public static $connection;

    public static function run(string $url, array $options = [], $resolverIp = '8.8.8.8')
    {
        $loop = Factory::create();
        static::$client =  new WebSocketClient($url, $loop, $options, $resolverIp);
        static::$connection = static::$client->connect();
        
        $loop->run();
    }

}
