<?php
namespace NotificationBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Extension class
 */
class NotificationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('notification_bunlde.slack.name', $config['slack']['name'] ?? null);
        $container->setParameter('notification_bunlde.slack.icon', $config['slack']['icon'] ?? null);
        $container->setParameter('notification_bunlde.slack.notifications', $config['slack']['notifications'] ?? []);
    }
}
