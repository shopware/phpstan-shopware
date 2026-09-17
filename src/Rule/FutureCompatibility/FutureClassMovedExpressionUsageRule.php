<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * PHPStan does not dispatch the class Name child of these expressions to Name rules.
 *
 * @implements Rule<Expr>
 * @internal
 */
final class FutureClassMovedExpressionUsageRule implements Rule
{
    public function __construct(private readonly ClassMovedUsage $classMovedUsage) {}

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $name = match (true) {
            $node instanceof Instanceof_,
            $node instanceof StaticCall,
            $node instanceof StaticPropertyFetch => $node->class instanceof Name ? $node->class : null,
            default => null,
        };

        if ($name === null) {
            return [];
        }

        $error = $this->classMovedUsage->error($name, $scope);

        return $error === null ? [] : [$error];
    }
}
