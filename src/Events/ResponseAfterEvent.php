<?php
/**
 * The Proton Micro Framework
 *
 * @author  Alex Bilbie <hello@alexbilbie.com>
 * @license MIT
 */
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