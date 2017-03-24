<?php
namespace ApiBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add tokenifier factory on tagged services
 */
class TokenifierPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('api_bundle.tokenify');

        foreach ($taggedServices as $sourceId => $tags) {
            $property = $tags[0]['property'] ?? 'accessToken';
            $required = $tags[0]['required'] ?? false;

            $container->setDefinition(
                $copyId = ($sourceId.'.not_tokenified'),
                $sourceDefinition = $container->getDefinition($sourceId)
            );

            $container
                ->register($sourceId, $sourceDefinition->getClass())
                ->setFactory([new Reference('api_bundle.tokenifier.factory'), 'tokenify'])
                ->setArguments([new Reference($copyId), $property, $required]);
        }
    }
}
