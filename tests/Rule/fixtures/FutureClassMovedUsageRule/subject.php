<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical;

class MovedSubject
{
    public static string $value = 'value';

    public static function call(): void {}
}
