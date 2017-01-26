<?php

namespace ApiBundle\Listener\Kernel;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
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
        if (!($result = $event->getControllerResult()) instanceof Response) {
            $event->setResponse($this->getResponse($result));
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $controller = $event->getRequest()->get('_controller');
        if (!empty($controller)) {
            list($controllerClass, $method) = explode('::', $controller);
            if ('\\'.$controllerClass instanceof ApiController) {
                if (($exception = $event->getException()) instanceof HttpExceptionInterface) {
                    $event->setResponse($this->getResponse(
                        ['error' => $exception->getMessage()],
                        $exception->getStatusCode()
                    ));
                }
            }
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
