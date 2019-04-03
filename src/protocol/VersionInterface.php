<?php

namespace nailfor\websocket\protocol;

interface VersionInterface 
{
    /**
     * Return protocol name
     * @return string
     */
    public function getProtocolIdentifierString() : string;

    /**
     * Return protocol version
     * @return int
     */
    public function getProtocolVersion() : int;
}