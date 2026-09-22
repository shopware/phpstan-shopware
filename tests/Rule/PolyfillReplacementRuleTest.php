<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Shopware\PhpStan\Rule\FutureCompatibility\PolyfillReplacementRule;

/**
 * @extends RuleTestCase<PolyfillReplacementRule>
 * @internal
 */
class PolyfillReplacementRuleTest extends RuleTestCase
{
    private ?bool $polyfillInstalled = true;

    public function testSuggestsProviderSideReplacementsWhenPolyfillIsInstalled(): void
    {
        $this->analyse([__DIR__ . '/fixtures/PolyfillReplacementRule/providers.php'], [
            ['Class "Shopware\\PhpStan\\Tests\\Fixture\\PolyfillReplacementRule\\LegacyProductStreamBuilder" implements deprecated "Shopware\\Core\\Content\\ProductStream\\Service\\ProductStreamBuilderInterface". The installed keulinho/shopware-polyfill package provides "Shopware\\Core\\Content\\ProductStream\\Service\\AbstractProductStreamBuilder" on older Shopware versions; extend it instead to use the replacement API now.', 16],
            ['Class "Shopware\\PhpStan\\Tests\\Fixture\\PolyfillReplacementRule\\LegacyNumberRangeValueGenerator" implements deprecated "Shopware\\Core\\System\\NumberRange\\ValueGenerator\\NumberRangeValueGeneratorInterface". The installed keulinho/shopware-polyfill package provides "Shopware\\Core\\System\\NumberRange\\ValueGenerator\\AbstractNumberRangeValueGenerator" on older Shopware versions; extend it instead to use the replacement API now.', 18],
            ['Class "Shopware\\PhpStan\\Tests\\Fixture\\PolyfillReplacementRule\\LegacyScheduledTaskHandler" overrides deprecated "Shopware\\Core\\Framework\\MessageQueue\\ScheduledTask\\ScheduledTaskHandler::rescheduleTask()". The installed keulinho/shopware-polyfill package provides "Shopware\\Core\\Framework\\MessageQueue\\ScheduledTask\\DynamicallyScheduledTaskHandler" on older Shopware versions; implement it and getNextExecutionTime() now, while keeping rescheduleTask() for older versions.', 22],
        ]);
    }

    public function testDoesNotSuggestReplacementsWhenPolyfillIsNotInstalled(): void
    {
        $this->polyfillInstalled = null;

        $this->analyse([__DIR__ . '/fixtures/PolyfillReplacementRule/providers.php'], []);
    }

    protected function getRule(): Rule
    {
        return new PolyfillReplacementRule($this->polyfillInstalled);
    }
}
