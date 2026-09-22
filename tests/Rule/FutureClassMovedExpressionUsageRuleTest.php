<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Shopware\PhpStan\Rule\FutureCompatibility\ClassMovedUsage;
use Shopware\PhpStan\Rule\FutureCompatibility\FutureClassMovedExpressionUsageRule;
use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical\MovedSubject;

/**
 * @extends RuleTestCase<FutureClassMovedExpressionUsageRule>
 * @internal
 */
class FutureClassMovedExpressionUsageRuleTest extends RuleTestCase
{
    public function testReportsOldClassNamesInExpressions(): void
    {
        $message = 'Class "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject" moved to "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Canonical\\MovedSubject". Use the new name now.';

        $this->analyse([__DIR__ . '/fixtures/FutureClassMovedUsageRule/usage.php'], [
            [$message, 18],
            [$message, 21],
            [$message, 22],
            [$message, 23],
            [$message, 34],
        ]);
    }

    protected function getRule(): Rule
    {
        return new FutureClassMovedExpressionUsageRule(new ClassMovedUsage([
            'Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject' => MovedSubject::class,
        ]));
    }
}
