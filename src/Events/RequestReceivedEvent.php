<?php
/**
 * The Proton Micro Framework
 *
 * @author  Alex Bilbie <hello@alexbilbie.com>
 * @license MIT
 */
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