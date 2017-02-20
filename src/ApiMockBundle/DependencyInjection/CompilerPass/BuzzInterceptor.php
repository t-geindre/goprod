<?php

namespace ApiMockBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuzzInterceptor implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $client = $container
            ->getDefinition('buzz.client')
            ->setClass('ApiMockBundle\\Buzz\\Client')
            ->addMethodCall('setHttpKernel', [new Reference('http_kernel')]);

        foreach ($container->getParameter('api_mock.localforward.hosts') as $host) {
            $client->addMethodCall('intercept', [$host]);
        }
    }
}
