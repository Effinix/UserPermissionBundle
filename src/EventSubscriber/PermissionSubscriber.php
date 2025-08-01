<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\EventSubscriber;

use Effinix\UserPermissionBundle\Attribute\Attribute\Routing\RequirePermission;
use Effinix\UserPermissionBundle\Logger\ConfigurableLogger;
use Psr\Cache\CacheItemPoolInterface;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PermissionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'cache.system')]
        private readonly CacheItemPoolInterface        $cache,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ConfigurableLogger            $logger,
        #[Autowire('%effinix.user_permission.cache%')]
        private readonly bool                          $performCaching,
    )
    {
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
            try {
                $reflection = new ReflectionMethod($controller, $method);
            } catch (ReflectionException $e) {
                $this->logger->error(sprintf(
                    'Reflection failed on %s::%s because of %s',
                    $controllerId,
                    $method,
                    $e->getMessage(),
                ));
                return;
            }

            $attributes = $reflection->getAttributes(RequirePermission::class);
            /**
             *
             */
            $permissions = array_map(function ($reflectionAttribute) {
                /** @var RequirePermission $attrib */
                $attrib = $reflectionAttribute->newInstance();
                return $attrib->permission;
            }, $attributes);

            $item->set($permissions);
            $this->cache->save($item);
        } else {
            $this->logger->info(sprintf(
                'Required controller (%s) permissions retrieved from cache',
                "$controllerId::$method",
            ));
            /** @var string[] $permissions */
            $permissions = $item->get();
        }

        foreach ($permissions as $permission) {
            if (!$this->authorizationChecker->isGranted($permission)) {
                $this->logger->alert(
                    "Authorization check failed for user",
                    [
                        'user' => $event->getRequest()->getUser(),
                    ]
                );
                throw new AccessDeniedHttpException("error.permission.missing: $permission");
            }
        }
    }

    private function getMethod(callable $routeHandler): callable
    {
        if (is_object($routeHandler)) return [$routeHandler, '__invoke'];
        return $routeHandler;
    }
}

