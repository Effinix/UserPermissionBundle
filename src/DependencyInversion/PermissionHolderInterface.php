<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\DependencyInversion;

interface PermissionHolderInterface
{
    /**
     * @return string[]
     */
    public function getPermissions(): array;

    public function hasPermission(string $permission): bool;
}
