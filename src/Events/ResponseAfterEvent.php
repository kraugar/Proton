<?php

namespace Proton\Events;

class ResponseAfterEvent extends ProtonEvent
{
    /**
     * (@inheritdoc)
     */
    public function getName()
    {
        return 'response.after';
    }
}