<?php
namespace AppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $root => $values) {
            foreach ($values as $key => $value) {
                $container->setParameter(sprintf('app_bundle.%s.%s', $root, $key), $value);
            }
        }
    }
}
