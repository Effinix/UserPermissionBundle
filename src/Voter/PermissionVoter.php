<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Voter;

use Effinix\UserPermissionBundle\DependencyInversion\PermissionHolderInterface;
use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;
use Effinix\UserPermissionBundle\Logger\ConfigurableLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PermissionVoter extends Voter
{
    public function __construct(
        private readonly Request $request,
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

        $retrievedSubject = $this->request->attributes->get($subject);
        if ($subject !== null && !$retrievedSubject) {
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

        /** @var PermissionHolderInterface $retrievedSubject */
        $retrievedSubject = $this->request->attributes->get($subject);
        return $retrievedSubject->hasPermission($attribute);
    }
}
