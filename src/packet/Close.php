<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class Close extends ControlPacket
{
    public const EVENT = 'CLOSE';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_CLOSE;
    }

}