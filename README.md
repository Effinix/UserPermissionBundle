# Easily protect routes with unique permissions

```php
src/Controller/Controller.php
<?php declare(strict_types=1);

namespace App\Controller;

use Effinix\UserPermissionBundle\Attribute\Attribute\Routing\RequirePermission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Controller
{
    #[Route(
        name: 'public_website',
    )]
    #[RequirePermission('login')]
    public function index()
    {
        return new Response("Hello world!");
    }
}
```

```php
src/Entity/User.php
<?php declare(strict_types=1);

namespace App\Entity;

use Effinix\UserPermissionBundle\DependencyInversion\UserInterface;

class User implements UserInterface
{
    ...
    public function hasPermission(string $permission) : bool {
        // check if the permission is valid for this user
        return in_array($permission, $this->getPermissions());
    }
    
    /**
     * @return string[]
     */
    public function getPermissions() : array {
        // domain logic way of getting user permissions (e.g. storing in the database)
        return ['login'];
    }
}
```

## Default config file:

```yaml
effinix_user_permission:
  permission_store:
    provider:
    permissions:
      - login
  do_cache: true

when@dev:
  effinix_user_permission:
    do_cache: false
```
