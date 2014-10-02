<?php

namespace Proton\Events;

class ResponseBeforeEvent extends ProtonEvent
{
    /**
     * (@inheritdoc)
     */
    public function getName()
    {
        return 'response.before';
    }
}