<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Voter;

use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    public function __construct(
        #[AutowireIterator('effinix.user_permission_bundle.permissions')]
        private iterable $permissions,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, iterator_to_array($this->permissions), true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) return false;

        return $user->hasPermission($attribute);
    }
}
