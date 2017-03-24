<?php

namespace ApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use ApiBundle\DependencyInjection\CompilerPass\TokenifierPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bundle class
 */
class ApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TokenifierPass());
    }
}
