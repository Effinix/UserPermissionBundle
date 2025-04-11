<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\DependencyInversion;

interface UserInterface
{
    public function hasPermission(string $permission): bool;

    /**
     * @return string[]
     */
    public function getPermissions(): array;
}
