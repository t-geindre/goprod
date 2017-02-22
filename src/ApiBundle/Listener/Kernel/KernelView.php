<?php

namespace ApiBundle\Listener\Kernel;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use ApiBundle\Controller\BaseController as ApiController;

/**
 * Serialize view
 */
class KernelView
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (!($result = $event->getControllerResult()) instanceof Response && !is_null($result)) {
            $event->setResponse($this->getResponse($result));
        }
    }

    /**
     * @param string  $content
     * @param integer $code
     *
     * @return Response
     */
    protected function getResponse($content, $code = 200) : Response
    {
        return new Response(
            $this->serializer->serialize($content, 'json'),
            $code,
            ['content-type' => 'application/json']
        );
    }
}
