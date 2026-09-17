<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @internal
 */
final class ClassMovedUsage
{
    private const ATTRIBUTE = 'Shopware\\Core\\Framework\\Deprecation\\BCChange\\ClassMoved';

    /**
     * @var array<lowercase-string, array{class: class-string, version: string}>|null
     */
    private ?array $classAliases = null;

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
        foreach (get_declared_classes() as $declaredClassName) {
            if (!str_starts_with(strtolower($declaredClassName), 'shopware\\')) {
                continue;
            }

            $reflection = new \ReflectionClass($declaredClassName);
            $canonicalClassName = $reflection->getName();
            if (strcasecmp($declaredClassName, $canonicalClassName) === 0) {
                continue;
            }

            foreach ($reflection->getAttributes(self::ATTRIBUTE) as $attribute) {
                $arguments = $attribute->getArguments();
                $previousClassName = $arguments['previousClassName'] ?? $arguments[1] ?? null;
                $version = $arguments['version'] ?? $arguments[0] ?? null;

                if (is_string($previousClassName) && is_string($version) && strcasecmp($previousClassName, $declaredClassName) === 0) {
                    $this->classAliases[strtolower($declaredClassName)] = [
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
