# PHP WebSocket Client

phpWebSocketClient is an WebSocket client library for PHP. Its based on the reactPHP socket-client and added the WebSocket protocol
specific functions. 

## Goal

Goal of this project is easy to use WebSocket client for PHP in a modern architecture without using any php modules.
* Protocol specifications: https://developer.mozilla.org/en-US/docs/Web/API/WebSockets_API/Writing_WebSocket_servers

## Example

```php

use nailfor\websocket\ClientFactory;

class WebSocketTest
{
    public function receiveMsg($reason)
    {
        echo "RECEIVE:";
        print_r($reason->getMessage());
    }

    public function afterConnect($reason)
    {
        $data = [
            "somedata" => "peesdata",
        ];
        //'topic' is a TOPIC_NAME from options
        ClientFactory::$client->publish('topic', $data);
    }

    public function errorMsg($reason)
    {
        echo $reason->getMessage(). PHP_EOL;
        exit;
    }
    
    public function closeMsg()
    {
        echo "closed!!!!". PHP_EOL;
        exit;
    }
    
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = [
            'topics' => [
                //TOPIC_NAME => events[]
                'topic' => [
                    'events' => [
                        //EVENT_NAME=>function 
                        'PUBLISH_RECEIVED'  => [$this, 'receiveMsg'],
                    ],
                ],
            ],
            'connect'   => [$this, 'afterConnect'],
            'timeout'   => [$this, 'errorMsg'],
            'error'     => [$this, 'errorMsg'],
            'close'     => [$this, 'closeMsg'],
        ];

        ClientFactory::run('ws://127.0.0.1:8080', $options);
    }
}

```

