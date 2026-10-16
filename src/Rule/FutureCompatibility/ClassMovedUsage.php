<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Shopware\Core\Framework\Deprecation\ClassAliasRegistry;

/**
 * @internal
 */
final class ClassMovedUsage
{
    private const POLYFILL_ALIAS_LOADER = 'Keulinho\\ShopwarePolyfill\\ClassAliasLoader';

    /**
     * @var array<lowercase-string, class-string>
     */
    private readonly array $aliases;

    /**
     * @param array<non-empty-string, class-string>|null $coreAliases
     * @param array<non-empty-string, class-string>|null $polyfillAliases
     */
    public function __construct(?array $coreAliases = null, ?array $polyfillAliases = null)
    {
        if ($coreAliases === null) {
            /** @var array<non-empty-string, class-string> $coreAliases */
            $coreAliases = class_exists(ClassAliasRegistry::class) ? ClassAliasRegistry::ALIASES : [];
        }

        if ($polyfillAliases === null) {
            /** @var array<non-empty-string, class-string> $polyfillAliases */
            $polyfillAliases = class_exists(self::POLYFILL_ALIAS_LOADER)
                ? constant(self::POLYFILL_ALIAS_LOADER . '::ALIASES')
                : [];
        }

        $normalizedAliases = [];
        foreach ([...$polyfillAliases, ...$coreAliases] as $previousClassName => $canonicalClassName) {
            $normalizedAliases[strtolower($previousClassName)] = $canonicalClassName;
        }

        $this->aliases = $normalizedAliases;
    }

    public function error(Name $name, Scope $scope): ?IdentifierRuleError
    {
        $className = $scope->resolveName($name);
        $canonicalClassName = $this->aliases[strtolower($className)] ?? null;
        if ($canonicalClassName === null) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            'Class "%s" moved to "%s". Use the new name now.',
            $className,
            $canonicalClassName,
        ))
            ->identifier('shopware.futureIncompatibility.classMoved')
            ->line($name->getStartLine())
            ->build();
    }
}
