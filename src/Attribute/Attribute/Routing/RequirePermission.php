<?php declare(strict_types=1);

namespace Effinix\UserPermissionBundle\Attribute\Attribute\Routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
readonly class RequirePermission
{
    public function __construct(
        public string $permission,
        public ?string $context = null,
    ) {
    }
}
