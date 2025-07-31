<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Voter;

use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;
use Effinix\UserPermissionBundle\Logger\ConfigurableLogger;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    public function __construct(
        #[AutowireIterator('effinix.user_permission_bundle.permissions')]
        private readonly iterable $permissions,
        private readonly ConfigurableLogger $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, iterator_to_array($this->permissions), true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            $this->logger->warning(
                "User '{$user->getUserIdentifier()}' does not implement ".UserInterface::class,
                [
                    'user' => $token,
                ],
            );
            return false;
        };

        $hasPermission = $user->hasPermission($attribute);
        $this->logger->info("User '{$user->getUserIdentifier()}' has $attribute permission", [
            'token' => $token,
        ]);
        return $hasPermission;
    }
}
