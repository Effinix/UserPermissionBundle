<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\EventSubscriber;

use Effinix\UserPermissionBundle\Attribute\Attribute\Routing\RequirePermission;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PermissionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'cache.system')]
        private readonly CacheItemPoolInterface $cache,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        #[Autowire('%effinix.user_permission.cache%')]
        private readonly bool $performCaching,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 20],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $routeHandler = $this->getMethod($event->getController());
        [$controller, $method] = $routeHandler;

        if (!is_object($controller) || !method_exists($controller, $method)) {
            return;
        }

        $controllerId = str_replace('\\', '_', $controller::class);
        $cacheKey = "effinix.route_permission.$controllerId.$method";
        $item = $this->cache->getItem($cacheKey);

        if (!$item->isHit() || !$this->performCaching) {
            $reflection = new ReflectionMethod($controller, $method);
            if (!$reflection) {
                return;
            }

            $attributes = $reflection->getAttributes(RequirePermission::class);
            /**
             *
             */
            $permissions = array_map(function($reflectionAttribute) {
                /** @var RequirePermission $attrib */
                $attrib = $reflectionAttribute->newInstance();
                return $attrib->permission;
            }, $attributes);

            $item->set($permissions);
            $this->cache->save($item);
        } else {
            /** @var string[] $permissions */
            $permissions = $item->get();
        }

        foreach ($permissions as $permission) {
            if (!$this->authorizationChecker->isGranted($permission)) {
                throw new AccessDeniedException("Missing permission: {$permission}");
            }
        }
    }

    private function getMethod(callable $routeHandler): callable
    {
        if (is_object($routeHandler)) return [$routeHandler, '__invoke'];
        return $routeHandler;
    }
}

