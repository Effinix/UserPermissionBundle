<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\DependencyInjection;

use Effinix\UserPermissionBundle\DependencyInversion\PermissionProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class UserPermissionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $permissions = [];
        if ($config['permission_store']['provider']) {
            $permProvider = $config['permission_store']['provider'];
            if (!is_subclass_of($permProvider, PermissionProviderInterface::class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Configured permission_provider "%s" must implement %s.',
                    $permProvider,
                    PermissionProviderInterface::class
                ));
            }

            $permissions = $permProvider::getPermissions();
        } else {
            $permissions = $config['permission_store']['permissions'] ?? [];
        }

        $container->setParameter('effinix.user_permission.permissions', $permissions);
        $container->setParameter('effinix.user_permission.cache', $config['do_cache'] == 'true');
        $container->setAlias('effinix.user_permission.logger', $config['logger']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );

        $loader->import('services.{yml,yaml}');
    }

    public function getAlias(): string {
        return 'effinix_user_permission';
    }
}
