<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PHPStan\Rules\Classes\InstantiationRule;
use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Methods\CallStaticMethodsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CallMethodsRule|CallStaticMethodsRule|InstantiationRule>
 * @internal
 */
class AnnouncedParameterCountIgnoreExtensionTest extends RuleTestCase
{
    private bool $staticCalls = false;
    private bool $constructors = false;

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/fixtures/AnnouncedParameterCountIgnoreExtension/extension.neon'];
    }

    public function testOnlyAnnouncedPositionalSlotsAreAllowed(): void
    {
        $subject = 'Shopware\\PhpStan\\Tests\\Fixture\\AnnouncedParameterCountIgnoreExtension\\AbstractRoute';
        $existing = 'Shopware\\PhpStan\\Tests\\Fixture\\AnnouncedParameterCountIgnoreExtension\\ExistingParameter';
        $this->analyse([__DIR__ . '/fixtures/AnnouncedParameterCountIgnoreExtension/calls.php'], [
            [sprintf('Method %s::get() invoked with 0 parameters, 1 required.', $subject), 43],
            [sprintf('Method %s::get() invoked with 3 parameters, 1 required.', $subject), 44],
            [sprintf('Method %s::get() invoked with 2 parameters, 0-1 required.', $existing), 45],
            [sprintf('Unknown parameter $criteria in call to method %s::get().', $subject), 46],
            [sprintf('Parameter #1 $id of method %s::get() expects int, string given.', $subject), 47],
        ]);
    }

    protected function getRule(): Rule
    {
        if ($this->staticCalls) {
            return self::getContainer()->getByType(CallStaticMethodsRule::class);
        }
        if ($this->constructors) {
            return self::getContainer()->getByType(InstantiationRule::class);
        }

        return self::getContainer()->getByType(CallMethodsRule::class);
    }

    public function testStaticCallsOnlyAllowAnnouncedSlots(): void
    {
        $this->staticCalls = true;
        $this->analyse([__DIR__ . '/fixtures/AnnouncedParameterCountIgnoreExtension/static-calls.php'], [
            ['Static method Shopware\\PhpStan\\Tests\\Fixture\\AnnouncedParameterCountIgnoreExtension\\StaticRoute::get() invoked with 3 parameters, 1 required.', 20],
        ]);
    }

    public function testConstructorsOnlyAllowAnnouncedSlots(): void
    {
        $this->constructors = true;
        $this->analyse([__DIR__ . '/fixtures/AnnouncedParameterCountIgnoreExtension/constructors.php'], [
            ['Class Shopware\\PhpStan\\Tests\\Fixture\\AnnouncedParameterCountIgnoreExtension\\Subject constructor invoked with 3 parameters, 1 required.', 20],
        ]);
    }
}
