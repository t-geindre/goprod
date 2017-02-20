<?php

namespace ApiMockBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ApiMockBundle\DependencyInjection\CompilerPass\BuzzInterceptor;

class ApiMockBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new BuzzInterceptor());
    }
}
