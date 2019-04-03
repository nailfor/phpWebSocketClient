<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class PublishReceived extends ControlPacket
{
    public const EVENT = 'PUBLISH_RECEIVED';
    
    public static function getControlPacketType()
    {
        return ControlPacketType::OP_TEXT;
    }
    
    public function parse()
    {
        parent::parse();
        try{
            $this->payload = json_decode($this->payload, true);
        }
        catch(\Exception $e){
            
        }
        return;
    }
    
    public function getTopic() : string
    {
        if (is_array($this->payload)){
            return $this->payload['topic'] ?? '';
        }
        return '';
    }
    
    public function getMessage()
    {
        if (is_array($this->payload)){
            return $this->payload['data'] ?? '';
        }
        return $this->payload;
    }
   
}