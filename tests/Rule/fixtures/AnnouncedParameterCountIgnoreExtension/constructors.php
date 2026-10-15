<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\AnnouncedParameterCountIgnoreExtension;

use Shopware\Core\Framework\Deprecation\BCChange\NewOptionalParameter;

class Subject
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'strict', parameterType: 'bool', defaultValue: false)]
    public function __construct(int $id) {}
}

class ConstructorCaller
{
    public function call(): void
    {
        new Subject(1, true);
        new Subject(1, true, 'extra');
    }
}
