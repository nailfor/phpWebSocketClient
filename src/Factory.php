<?php

namespace nailfor\websocket;

use nailfor\websocket\packet\Close;
use nailfor\websocket\packet\ConnectionAck;
use nailfor\websocket\packet\PublishReceived;
use nailfor\websocket\packet\Ping;
use nailfor\websocket\packet\TimeOut;
use nailfor\websocket\protocol\Violation;

class Factory
{
    protected static $connect = false;
    
    public static function getNextPacket($remainingData)
    {
        $packet = self::getPacketByMessage($remainingData);
        yield $packet;
    }

    protected static function getPacketType($input) : int
    {
        if (!static::$connect) {
            $headers = ConnectionAck::getHeaders($input);
            $responce = explode(" ", $headers[0] ?? []);
            return $responce[1] ?? null;
        }
        return ConnectionAck::getOpcode($input);

    }
    
    protected static function getPacketByMessage($input)
    {
        $controlPacketType = static::getPacketType($input);
        
        $packets = [
            TimeOut::class,
            ConnectionAck::class,
            PublishReceived::class,
            Ping::class,
            Close::class,
        ];
        
        foreach($packets as $packet){
            if ($controlPacketType == $packet::getControlPacketType()){
                $instance = $packet::parsePacket($input);
                if (method_exists($instance, 'isConnect')) {
                    static::$connect = $instance->isConnect();
                }
                return $instance;
            }
        }

        throw new Violation('Unexpected packet type: ' . $controlPacketType);
    }

}
