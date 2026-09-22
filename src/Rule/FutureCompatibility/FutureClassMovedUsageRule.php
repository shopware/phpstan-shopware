<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @implements Rule<Name>
 * @internal
 */
final class FutureClassMovedUsageRule implements Rule
{
    public function __construct(private readonly ClassMovedUsage $classMovedUsage) {}

    public function getNodeType(): string
    {
        return Name::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $error = $this->classMovedUsage->error($node, $scope);

        return $error === null ? [] : [$error];
    }
}
