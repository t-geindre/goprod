<?php

namespace ApiMockBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ApiMockBundle\DependencyInjection\CompilerPass\BuzzInterceptor;

/**
 * Bundle class
 */
class ApiMockBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BuzzInterceptor());
    }
}
