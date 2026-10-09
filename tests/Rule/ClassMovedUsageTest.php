<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPUnit\Framework\TestCase;
use Shopware\PhpStan\Rule\FutureCompatibility\ClassMovedUsage;
use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical\MovedSubject;

/** @internal */
class ClassMovedUsageTest extends TestCase
{
    private const LEGACY_CLASS = 'Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject';

    public function testIncludesAliasesDeclaredOnlyByPolyfill(): void
    {
        $usage = new ClassMovedUsage([], [self::LEGACY_CLASS => MovedSubject::class]);

        $error = $usage->error(new Name(self::LEGACY_CLASS), $this->scope());

        static::assertNotNull($error);
        static::assertSame(
            'Class "' . self::LEGACY_CLASS . '" moved to "' . MovedSubject::class . '". Use the new name now.',
            $error->getMessage(),
        );
    }

    public function testCoreAliasTakesPrecedenceOverPolyfillAlias(): void
    {
        $usage = new ClassMovedUsage(
            [self::LEGACY_CLASS => MovedSubject::class],
            [self::LEGACY_CLASS => \stdClass::class],
        );

        $error = $usage->error(new Name(self::LEGACY_CLASS), $this->scope());

        static::assertNotNull($error);
        static::assertSame(
            'Class "' . self::LEGACY_CLASS . '" moved to "' . MovedSubject::class . '". Use the new name now.',
            $error->getMessage(),
        );
    }

    private function scope(): Scope
    {
        $scope = $this->createMock(Scope::class);
        $scope->method('resolveName')->willReturn(self::LEGACY_CLASS);

        return $scope;
    }
}
