<?php

namespace nailfor\websocket;

use nailfor\websocket\packet\Connect;
use nailfor\websocket\packet\ConnectionAck;
use nailfor\websocket\packet\ControlPacket;
use nailfor\websocket\packet\Close;
use nailfor\websocket\packet\Publish;
use nailfor\websocket\packet\Ping;
use nailfor\websocket\packet\Pong;
use nailfor\websocket\packet\TimeOut;

use nailfor\websocket\protocol\Violation;

use React\EventLoop\LoopInterface as Loop;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface as Connection;
use React\Socket\ConnectorInterface as ReactConnector;
use React\Socket\DnsConnector;
use React\Socket\SecureConnector;
use React\Socket\TcpConnector;
use React\Dns\Resolver\Factory as DNSFactory;

class WebSocketClient
{
    /**
     * @var $loop Loop
     */
    private $url;
    private $loop;
    private $socketConnector;
    private $options;
    private $stream;

    public function __construct(string $url, Loop $loop, array $options, string $resolverIp = '8.8.8.8')
    {
        
        $pos = strpos($url, ':');
        $protocol = substr($url, 0, $pos);
        $url = substr($url, $pos+3);
        $this->url = 'tcp://'.$url;

        $connector = self::createDnsConnector($resolverIp, $loop);
        if ($protocol == 'wss'){
            $connector = new SecureConnector($connector, $loop);
        }
        
        $this->socketConnector = $connector;
        $this->loop = $loop;

        $this->options = new \stdClass;
        if($options) {
            foreach($options as $key=>$val) {
                $this->options->{$key} = $val;
            }
        }
        $this->options->url = $url;
    }

    private static function createDnsConnector($resolverIp, $loop)
    {
        $dnsResolverFactory = new DNSFactory();
        $resolver = $dnsResolverFactory->createCached($resolverIp, $loop);

        return new DnsConnector(new TcpConnector($loop), $resolver);
    }
    
    
    /**
     * Creates a new connection
     *
     *
     * @return PromiseInterface Resolves to a \React\Stream\Stream once a connection has been established
     */
    public function connect() 
    {
        $promise = $this->socketConnector->connect($this->url);
        $promise->then(function(Connection $stream){
            return $this->listenForPackets($stream);
        });

        $connection = $promise->then(function(Connection $stream){
            return $this->sendConnectPacket($stream);
        });

        return $connection;
    }

    protected function listenForPackets(Connection $stream)
    {
        $this->stream = $stream;
        
        $this->setEvents($stream);
        $stream->on('data', function($rawData) use ($stream) {
            try {
                foreach (Factory::getNextPacket($rawData) as $packet) {
                    $event = $packet::EVENT;
                    if (method_exists($packet, 'getTopic')) {
                        $topic = $packet->getTopic();
                        $event .= ":$topic";
                    }
                    $stream->emit($event, [$packet]);
                }
            }
            catch (Violation $e) {
                //TODO Actually, the spec says to disconnect if you receive invalid data.
                $stream->emit('INVALID', [$e]);
            }
        });
    }
    
    protected function setEvents(Connection $stream)
    {
        $topics = $this->options->topics ?? [];
        foreach ($topics as $topic => $params){
            foreach ($params['events'] as $event => $closure) {
                $stream->on("$event:$topic", $closure);
            }
        }
        
        if (isset($this->options->connect)) {
            $stream->on(ConnectionAck::EVENT, $this->options->connect);
        }
        if (isset($this->options->close)) {
            $stream->on(Close::EVENT, $this->options->close);
            $stream->on('close', $this->options->close);
        }
        if (isset($this->options->error)) {
            $stream->on('error', $this->options->error);
        }
        if (isset($this->options->timeout)) {
            $stream->on(TimeOut::EVENT, $this->options->timeout);
        }
        $stream->on(Ping::EVENT, function($packet){
            $payload = $packet->getPayload();
            $packet = new Pong($payload);
            $this->sendPacketToStream($packet);
        });
        
    }

    /**
     * @return \React\Promise\Promise
     */
    public function sendConnectPacket(Connection $stream) {
        $packet = new Connect($this->options);
        $this->sendPacketToStream($packet);

        $deferred = new Deferred();
        $stream->on(ConnectionAck::EVENT, function($message) use ($stream, $deferred) {
            return $deferred->resolve($stream);
        });

        return $deferred->promise();
    }

    protected function sendPacketToStream(ControlPacket $controlPacket)
    {
        $message = $controlPacket->get();
        //echo "send: $message\n";
        return $this->stream->write($message);
    }

    /**
     * @return \React\Promise\Promise
     */
    public function publish($topic, $message)
    {
        $packet = new Publish($topic, $message);
        $this->sendPacketToStream($packet);
    }

}
