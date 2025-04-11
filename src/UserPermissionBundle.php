<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle;

use Effinix\UserPermissionBundle\DependencyInjection\UserPermissionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class UserPermissionBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new UserPermissionExtension();
    }
}
