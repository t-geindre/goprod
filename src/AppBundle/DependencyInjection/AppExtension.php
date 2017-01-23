<?php
namespace AppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->flatten($config, 'app_bundle', $container);
    }

    protected function flatten(array $arr, $parentKey, ContainerBuilder $container)
    {
        foreach ($arr as $key => $value) {
            $key = $parentKey.'.'.$key;
            if (is_array($value)) {
                $this->flatten($value, $key, $container);
                continue;
            }
            $container->setParameter($key, $value);
        }
    }
}
