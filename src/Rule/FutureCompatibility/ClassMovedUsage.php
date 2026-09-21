<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Shopware\Core\Framework\Deprecation\ClassAliasRegistry;

/**
 * @internal
 */
final class ClassMovedUsage
{
    private const ATTRIBUTE = 'Shopware\\Core\\Framework\\Deprecation\\BCChange\\ClassMoved';

    /**
     * @var array<non-empty-string, class-string>
     */
    private readonly array $aliases;

    /**
     * @var array<lowercase-string, array{class: class-string, version: string}>|null
     */
    private ?array $classAliases = null;

    /**
     * @param array<non-empty-string, class-string>|null $aliases
     */
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        ?array $aliases = null,
    ) {
        if ($aliases !== null || !class_exists(ClassAliasRegistry::class)) {
            $this->aliases = $aliases ?? [];

            return;
        }

        /** @var array<non-empty-string, class-string> $registryAliases */
        $registryAliases = ClassAliasRegistry::ALIASES;
        $this->aliases = $registryAliases;
    }

    public function error(Name $name, Scope $scope): ?IdentifierRuleError
    {
        $className = $scope->resolveName($name);
        $movedClass = $this->classAliases()[strtolower($className)] ?? null;
        if ($movedClass === null || $this->isDeprecatedInVersion($scope, $movedClass['version'])) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            'Class "%s" moved to "%s" and the old name will be removed in %s. Use the new name now.',
            $className,
            $movedClass['class'],
            $movedClass['version'],
        ))
            ->identifier('shopware.futureIncompatibility.classMoved')
            ->line($name->getStartLine())
            ->build();
    }

    /**
     * @return array<lowercase-string, array{class: class-string, version: string}>
     */
    private function classAliases(): array
    {
        if ($this->classAliases !== null) {
            return $this->classAliases;
        }

        $this->classAliases = [];
        foreach ($this->aliases as $previousClassName => $canonicalClassName) {
            if (!$this->reflectionProvider->hasClass($canonicalClassName)) {
                continue;
            }

            $reflection = $this->reflectionProvider->getClass($canonicalClassName)->getNativeReflection();
            foreach ($reflection->getAttributes() as $attribute) {
                if ($attribute->getName() !== self::ATTRIBUTE) {
                    continue;
                }

                $arguments = $attribute->getArguments();
                $attributePreviousClassName = $arguments['previousClassName'] ?? $arguments[1] ?? null;
                $version = $arguments['version'] ?? $arguments[0] ?? null;

                if (is_string($attributePreviousClassName)
                    && is_string($version)
                    && strcasecmp($attributePreviousClassName, $previousClassName) === 0
                ) {
                    $this->classAliases[strtolower($previousClassName)] = [
                        'class' => $canonicalClassName,
                        'version' => $version,
                    ];
                }
            }
        }

        return $this->classAliases;
    }

    private function isDeprecatedInVersion(Scope $scope, string $version): bool
    {
        return $this->hasDeprecationTagForVersion($scope->getClassReflection()?->getDeprecatedDescription(), $version)
            || $this->hasDeprecationTagForVersion($scope->getFunction()?->getDeprecatedDescription(), $version);
    }

    private function hasDeprecationTagForVersion(?string $description, string $version): bool
    {
        return $description !== null && preg_match(sprintf('/(?:^|\\s)tag:%s(?:\\s|$)/', preg_quote($version, '/')), $description) === 1;
    }
}
