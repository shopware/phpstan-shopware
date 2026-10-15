<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\AnnouncedParameterCountIgnoreExtension;

use Shopware\Core\Framework\Deprecation\BCChange\NewOptionalParameter;

class StaticRoute
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'criteria', parameterType: 'string', defaultValue: '')]
    public static function get(int $id): void {}
}

class StaticCaller
{
    public function call(): void
    {
        StaticRoute::get(1, 'criteria');
        StaticRoute::get(1, 'criteria', true);
    }
}
