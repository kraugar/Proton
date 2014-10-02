<?php

namespace Proton\Events;

class RequestReceivedEvent extends ProtonEvent
{
    /**
     * (@inheritdoc)
     */
    public function getName()
    {
        return 'request.received';
    }
}