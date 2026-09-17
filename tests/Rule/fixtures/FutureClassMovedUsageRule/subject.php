<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical;

#[\Shopware\Core\Framework\Deprecation\BCChange\ClassMoved(
    version: 'v6.8.0',
    previousClassName: 'Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject',
)]
class MovedSubject
{
    public static string $value = 'value';

    public static function call(): void {}
}
