<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Shopware\PhpStan\Rule\FutureCompatibility\ClassMovedUsage;
use Shopware\PhpStan\Rule\FutureCompatibility\FutureClassMovedExpressionUsageRule;
use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical\MovedSubject;

/**
 * @extends RuleTestCase<FutureClassMovedExpressionUsageRule>
 * @internal
 */
class FutureClassMovedExpressionUsageRuleTest extends RuleTestCase
{
    #[RunInSeparateProcess]
    public function testReportsOldClassNamesInExpressions(): void
    {
        class_alias(MovedSubject::class, 'Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject');

        $message = 'Class "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Legacy\\MovedSubject" moved to "Shopware\\PhpStan\\Tests\\Fixture\\FutureClassMovedUsageRule\\Canonical\\MovedSubject" and the old name will be removed in v6.8.0. Use the new name now.';

        $this->analyse([__DIR__ . '/fixtures/FutureClassMovedUsageRule/usage.php'], [
            [$message, 21],
            [$message, 22],
            [$message, 23],
        ]);
    }

    protected function getRule(): Rule
    {
        return new FutureClassMovedExpressionUsageRule(new ClassMovedUsage());
    }
}
