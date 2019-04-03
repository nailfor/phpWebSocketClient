<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class Publish extends ControlPacket
{
    public const EVENT = 'PUBLISH';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_TEXT;
    }
    
    public function __construct(string $topic, $data)
    {
        $this->payload = json_encode([
            'topic'=> $topic,
            'data' => $data,
        ]);
        
    }
    
}