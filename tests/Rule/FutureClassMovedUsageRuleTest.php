<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Shopware\PhpStan\Rule\FutureCompatibility\ClassMovedUsage;
use Shopware\PhpStan\Rule\FutureCompatibility\FutureClassMovedUsageRule;
use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical\MovedSubject;

/**
 * @extends RuleTestCase<FutureClassMovedUsageRule>
 * @internal
 */
class FutureClassMovedUsageRuleTest extends RuleTestCase
{
    public function testReportsOldClassNames(): void
    {
        $message = 'Class "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject" moved to "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Canonical\\MovedSubject" and the old name will be removed in v6.8.0. Use the new name now.';

        $this->analyse([__DIR__ . '/fixtures/FutureClassMovedUsageRule/usage.php'], [
            [$message, 12],
            [$message, 16],
            [$message, 16],
            [$message, 19],
        ]);
    }

    protected function getRule(): Rule
    {
        return new FutureClassMovedUsageRule(new ClassMovedUsage(self::createReflectionProvider(), [
            'Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject' => MovedSubject::class,
        ]));
    }
}
