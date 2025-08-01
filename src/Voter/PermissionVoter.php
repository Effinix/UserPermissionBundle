<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Voter;

use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;
use Effinix\UserPermissionBundle\Logger\ConfigurableLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    public function __construct(
        #[Autowire(param: 'effinix.user_permission.permissions')]
        private readonly array $permissions,
        private readonly ConfigurableLogger $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        $supports = in_array($attribute, $this->permissions, true);
        if (!$supports) {
            $this->logger->warning("PermissionVoter abstained due to not supporting $attribute", [
                'supports' => $this->permissions,
            ]);
        }
        return $supports;
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
