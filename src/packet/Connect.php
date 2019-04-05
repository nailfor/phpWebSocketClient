<?php

namespace nailfor\websocket\packet;

use nailfor\websocket\protocol\Version;

/**
 * After a Network Connection is established by a Client to a Server, the
 * first Packet sent from the Client to the Server MUST be a CONNECT Packet.
 */
class Connect extends ControlPacket 
{
    /** @var null|string  */
    protected $path = '/';

    /** @var null|string  */
    protected $orogin = '/';

    /** @var null|string  */
    protected $url;
    
    /**
     * @param array $options
     */
    public function __construct($options) {
        parent::__construct();
        
        $this->path     = $options->path ?? '/';
        $this->origin   = $options->origin ?? 'null';
        $this->url      = $options->url;
        static::$key    = $this->generateToken();
    }

    /**
     * @return string
     */
    public function get() : string
    {
        $version = new Version;
        
        $header = [
            'GET'                       => $this->path . ' HTTP/1.1',
            'Host:'                     => $this->url,
            'Origin:'                   => $this->origin,
            'User-Agent:'               => 'PHPWebSocketClient',
            'Connection:'               => 'keep-alive, Upgrade',
            'Upgrade:'                  => $version->getProtocolIdentifierString(),
//            'Sec-WebSocket-Protocol:'  => 'wamp',
            'Sec-WebSocket-Version:'    => $version->getProtocolVersion(),
            'Sec-WebSocket-Key:'        => static::$key,
        ];
        
        $result = '';
        foreach ($header as $key => $value )
        {
            $result .= "$key $value\r\n";
        }
        return "$result\r\n";
    }
    
    protected function generateToken()
    {
        $length = 16;
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';

        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < $length; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, $length);

        return base64_encode($randomString);
    }     
    
}
