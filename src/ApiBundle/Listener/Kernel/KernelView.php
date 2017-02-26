<?php

namespace ApiBundle\Listener\Kernel;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

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
        if (!($result = $event->getControllerResult()) instanceof Response) {
            $controller = $event->getRequest()->attributes->get('_controller');
            if (empty($controller) || strpos($controller, '::') === false) {
                return;
            }

            list($class, $method) = explode('::', $controller);
            if (!is_subclass_of($class, "ApiBundle\Controller\BaseController")) {
                return;
            }

            $config = $event->getRequest()->attributes->get('_serializer') ?? [];
            $context = SerializationContext::create()->setGroups($config['groups'] ?? ['public']);

            $event->setResponse(
                new Response(
                    $this->serializer->serialize($result, 'json', $context),
                    200,
                    ['content-type' => 'application/json']
                )
            );
        }
    }
}
