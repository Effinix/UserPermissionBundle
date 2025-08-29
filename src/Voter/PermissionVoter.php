<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Voter;

use Effinix\UserPermissionBundle\DependencyInversion\PermissionHolderInterface;
use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;
use Effinix\UserPermissionBundle\Logger\ConfigurableLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{

    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(param: 'effinix.user_permission.permissions')]
        private readonly array $permissions,
        private readonly ConfigurableLogger $logger,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, $this->permissions, true)) {
            $this->logger->warning("PermissionVoter abstained due to not supporting $attribute", [
                'supports' => $this->permissions,
            ]);
            return false;
        }

        if (!$subject) return true;

        $retrievedSubject = $this->requestStack->getCurrentRequest()->attributes->get($subject);
        if (!$retrievedSubject) {
            $this->logger->warning("PermissionVoter abstained due to not finding subject $subject", [
                'subject' => $retrievedSubject,
            ]);
            return false;
        }

        if (!$retrievedSubject instanceof PermissionHolderInterface) {
            $this->logger->warning("PermissionVoter abstained due to $subject not implementing PermissionHolderInterface");
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user) return false;
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
        if ($hasPermission) {
            $this->logger->info("User '{$user->getUserIdentifier()}' has $attribute permission", [
                'token' => $token,
            ]);
            return true;
        }

        if ($subject) {
            /** @var PermissionHolderInterface $retrievedSubject */
            $retrievedSubject = $this->requestStack->getCurrentRequest()->attributes->get($subject);
            return $retrievedSubject->hasPermission($attribute);
        }

        return false;
    }
}
