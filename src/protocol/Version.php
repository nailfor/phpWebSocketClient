<?php

namespace nailfor\websocket\protocol;

class Version implements VersionInterface
{

    /**
     * {@inheritdoc}
     */
    public function getProtocolIdentifierString() : string
    {
        return 'websocket';
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion() : int
    {
        return 13;
    }
}