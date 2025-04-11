<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\DependencyInversion;

interface PermissionProviderInterface
{
    /**
     * @return string[]
     */
    public static function getPermissions(): array;
}
