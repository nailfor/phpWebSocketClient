<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Violation;

class ConnectionAck extends ControlPacket
{
    public const EVENT = 'CONNECTION_ASK';
    protected const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    
    protected $connected = false;
    protected static $headers = [];
    
    public static function getControlPacketType()
    {
        return ControlPacketType::CONNECT;
    }
    
    public function parse()
    {
        $result = [];
        foreach(static::$headers as $header){
            $pos = strpos($header, ':');
            if ($pos === false) {
                continue;
            }
            $key = substr($header, 0, $pos);
            $val = trim(substr($header, $pos+1));
            $result[$key] = $val;
        }
        $this->connected = $this->checkConnection($result);
    }

    protected function checkConnection($headers)
    {
        $responce = [
            'Connection'    => 'Upgrade',
            'Upgrade'       => 'websocket',
            'Sec-WebSocket-Accept'  => $this->calcKey(),
        ];
        
        foreach($responce as $key => $val){
            if (!isset($headers[$key]) || $headers[$key] != $val) {
                return false;
            }
        }
        return true;
        
    }
    
    protected function calcKey()
    {
        return base64_encode(sha1(static::$key . static::GUID, true));
    }

    
    public function isConnect()
    {
        return $this->connected;
    }

    public static function getHeaders($input) : array
    {
        $headers = explode("\r\n", $input);
        if (!$headers) {
            return [];
        }
        static::$headers = $headers;
        return $headers;
        
    }
    
}