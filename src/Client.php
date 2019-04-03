<?php
namespace nailfor\websocket;


class Client
{
    private $client;

    public function onWelcome(array $data)
    {
        echo "welcome\n";
    }

    public function onEvent($topic, $message)
    {
        echo "event\n";
    }

    public function subscribe($topic)
    {
        echo "subscribe\n";
        $this->client->subscribe($topic);
    }

    public function unsubscribe($topic)
    {
        echo "unsubscribe\n";
        $this->client->unsubscribe($topic);
    }

    public function call($proc, $args, Closure $callback = null)
    {
        echo "call\n";
        $this->client->call($proc, $args, $callback);
    }

    public function publish($topic, $message)
    {
        echo "publish\n";
        $this->client->publish($topic, $message);
    }

    public function setClient($client)
    {
        $this->client = $client;
    }    
}
