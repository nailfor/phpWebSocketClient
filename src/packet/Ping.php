<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class Ping extends ControlPacket
{
    public const EVENT = 'PING';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_PING;
    }
}