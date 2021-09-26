<?php

namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CORSHeaderSubscriber implements EventSubscriberInterface
{

    protected $headers = [
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Allow-Methods'     => 'GET,HEAD,OPTIONS,POST,PUT',
        'Access-Control-Allow-Headers'     => 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization'
    ];

    public function onKernelResponse(ResponseEvent $event)
    {

        $response = $event->getResponse();

        $response->headers->add($this->headers);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
