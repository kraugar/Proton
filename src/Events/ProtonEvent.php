<?php

namespace Proton\Events;

use League\Event\AbstractEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ProtonEvent extends AbstractEvent
{
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response = null)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest()
    {
        return $request;
    }

    public function getResponse()
    {
        return $response;
    }

    public function getName()
    {
        return self::NAME;
    }
}