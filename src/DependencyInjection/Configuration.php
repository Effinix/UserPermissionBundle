<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\DependencyInjection;

use Effinix\UserPermissionBundle\DependencyInversion\PermissionProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Contracts\Cache\CacheInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('effinix_user_permission');

        $rootNode = $treeBuilder->getRootNode();

        // @formatter:off -- this is here to keep indentation below
        // @phpcs:disable
        $rootNode->children()
            ->arrayNode('permission_store')
                ->children()
                    ->scalarNode('provider')
                        ->defaultNull()
                        ->info(sprintf('FQCN of a class implementing %s.', PermissionProviderInterface::class))
                    ->end()
                    ->arrayNode('permissions')
                        ->defaultValue([])
                        ->prototype('scalar')->end()
                        ->info('List of permission identifier strings.')
                    ->end()
                ->end()
                ->validate()
                    ->ifTrue(function ($cfg) {
                        if ($cfg['provider'] !== null && !empty($cfg['permissions'])) {
                            return true;
                        }

                        return false;
                    })
                    ->thenInvalid('You must define either "permissions" or "permission_provider" but not both.')
                ->end()
            ->end()
            ->scalarNode('do_cache')
                ->defaultFalse()
                ->info("Should we cache the route permission reflection step? (recommended for production)")
            ->end()
            ->scalarNode('logger')
                ->defaultValue('effinix.user_permission.logger.null')
                ->info("Logger which implements Psr\\Log\\LoggerInterface.")
            ->end()
        ->end();
        // @phpcs:enable
        // @formatter:on -- format the rest of the file

        return $treeBuilder;
    }
}
