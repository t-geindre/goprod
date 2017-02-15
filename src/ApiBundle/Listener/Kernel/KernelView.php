<?php

namespace ApiBundle\Listener\Kernel;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use ApiBundle\Controller\BaseController as ApiController;

class KernelView
{
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (!($result = $event->getControllerResult()) instanceof Response && !is_null($result)) {
            $event->setResponse($this->getResponse($result));
        }
    }

    protected function getResponse($content, $code = 200)
    {
        return new Response(
            $this->serializer->serialize($content, 'json'),
            $code,
            ['content-type' => 'application/json']
        );
    }
}
