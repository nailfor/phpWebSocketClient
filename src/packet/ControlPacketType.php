<?php

namespace nailfor\websocket\packet;

class ControlPacketType 
{
    const OP_FRAGMENT   = 0;
    const OP_TEXT       = 1;
    const OP_BINARY     = 2;
    const OP_CLOSE      = 8;
    const OP_PING       = 9;
    const OP_PONG       = 10;
    
    
    const CONNECT = 101;
    const TIME_OUT = 504;
}