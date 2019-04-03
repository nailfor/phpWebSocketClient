<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class Binary extends ControlPacket
{
    public const EVENT = 'BINARY';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_BINARY;
    }
   
}