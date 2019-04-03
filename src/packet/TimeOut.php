<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class TimeOut extends ControlPacket
{
    public const EVENT = 'TIME_OUT';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::TIME_OUT;
    }
    
    public function getMessage()
    {
        return 'time out';
    }
    
}