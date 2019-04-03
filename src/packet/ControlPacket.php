<?php

namespace nailfor\websocket\packet;

abstract class ControlPacket 
{
    protected static $key;
    
    protected $payload = '';

    protected $identifier;
    
    public $rawData = '';

    public function __construct()
    {
    }

    public function parse()
    {
        $this->payload = $this->hybi10Decode();
        return $this;
    }

    /**
     * @param Version $version
     * @param string $rawInput
     * @return static
     */
    public static function parsePacket($rawInput)
    {
        $packet = new static();
        $packet->rawData = $rawInput;
        $packet->parse();
        return $packet;
    }
    
    public function get() : string
    {
        return $this->hybi10Encode();
    }
    
    public function getPayload()
    {
        return $this->payload;
    }
    
    /**
     * @param $payload
     * @param string $type
     * @param bool $masked
     * @return bool|string
     */
    protected function hybi10Encode($masked = true) : string
    {
        $payload = $this->payload;
        $type = static::getControlPacketType();
        $frameHead = [];
        $payloadLength = strlen($payload);
        switch ($type) {
            case ControlPacketType::OP_TEXT:
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;
            case ControlPacketType::OP_CLOSE:
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;
            case ControlPacketType::OP_PING:
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;
            case ControlPacketType::OP_PONG:
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }
        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }
            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                throw new \RuntimeException('Invalid payload. Could not encode frame.');
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }
        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }
        if ($masked === true) {
            // generate a random mask:
            $mask = [];
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }
            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }
        return $frame;
    }

    // estimate frame type:
    public static function getOpcode($data) : int
    {
        $firstByteBinary = sprintf('%08b', ord($data[0]));
        return bindec(substr($firstByteBinary, 4, 4));
    }
    
    /**
     * @return null|string
     */
    public function hybi10Decode() : string
    {
        $unmaskedPayload = '';
        $secondByteBinary = sprintf('%08b', ord($this->rawData[1]));
        $isMasked = ($secondByteBinary[0] == '1') ? true : false;
        $payloadLength = ord($this->rawData[1]) & 0x7F;
        if ($payloadLength === 126) {
            $mask = substr($this->rawData, 4, 4);
            $payloadOffset = 8;
            $dataLength = bindec(sprintf('%08b', ord($this->rawData[2])) . sprintf('%08b', ord($this->rawData[3]))) + $payloadOffset;
        } elseif ($payloadLength === 127) {
            $mask = substr($this->rawData, 10, 4);
            $payloadOffset = 14;
            $tmp = '';
            for ($i = 0; $i < 8; $i++) {
                $tmp .= sprintf('%08b', ord($this->rawData[$i + 2]));
            }
            $dataLength = bindec($tmp) + $payloadOffset;
            unset($tmp);
        } else {
            $mask = substr($this->rawData, 2, 4);
            $payloadOffset = 6;
            $dataLength = $payloadLength + $payloadOffset;
        }
        if ($isMasked === true) {
            for ($i = $payloadOffset; $i < $dataLength; $i++) {
                $j = $i - $payloadOffset;
                if (isset($this->rawData[$i])) {
                    $unmaskedPayload .= $this->rawData[$i] ^ $mask[$j % 4];
                }
            }
            $payload = $unmaskedPayload;
        } else {
            $payloadOffset = $payloadOffset - 4;
            $payload = substr($this->rawData, $payloadOffset);
        }
        return $payload;
    }    
  
}
