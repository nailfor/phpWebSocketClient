<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class Pong extends ControlPacket
{
    public const EVENT = 'PONG';
    
    public function __construct($payload)
    {
        $this->payload = $payload;
    }
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_PONG;
    }
    
}