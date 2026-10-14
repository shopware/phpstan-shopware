<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

/**
 * Allows announced positional parameters without treating the current signature as variadic.
 * Their types and future requiredness are checked by FutureCallSiteRule.
 *
 * PHPStan resolves accessible native methods before consulting MethodsClassReflectionExtension,
 * so that extension cannot append parameters to an existing native signature. Filter only the
 * count diagnostic here and retain the announced-parameter checks in FutureCallSiteRule.
 *
 * @internal
 */
final class AnnouncedParameterCountIgnoreExtension implements IgnoreErrorExtension
{
    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if ($error->getIdentifier() !== 'arguments.count') {
            return false;
        }

        if (($node instanceof MethodCall || $node instanceof NullsafeMethodCall) && $node->name instanceof Identifier) {
            $type = $scope->getType($node->var);
            $name = $node->name->toString();
        } elseif ($node instanceof StaticCall && $node->class instanceof Name && $node->name instanceof Identifier) {
            $type = $scope->resolveTypeByName($node->class);
            $name = $node->name->toString();
        } elseif ($node instanceof New_ && $node->class instanceof Name) {
            $type = $scope->resolveTypeByName($node->class);
            $name = '__construct';
        } else {
            return false;
        }

        foreach ($node->getArgs() as $argument) {
            // Named arguments are not supported by a positional BC shim; unpacking has an unknown count.
            if ($argument->name !== null || $argument->unpack) {
                return false;
            }
        }

        $reflection = $scope->getMethodReflection($type, $name);
        if ($reflection === null) {
            return false;
        }

        $class = $reflection->getDeclaringClass()->getNativeReflection();
        if (!$class->hasMethod($name)) {
            return false;
        }

        $method = $class->getMethod($name);
        $parameters = $method->getParameters();
        $count = count($node->getArgs());
        if ($count <= count($parameters) || $method->isVariadic()) {
            return false;
        }

        $existingNames = array_map(static fn(\ReflectionParameter $parameter): string => $parameter->getName(), $parameters);
        $announcedNames = [];
        foreach ($method->getAttributes() as $attribute) {
            if (!in_array($attribute->getName(), [
                'Shopware\\Core\\Framework\\Deprecation\\BCChange\\NewRequiredParameter',
                'Shopware\\Core\\Framework\\Deprecation\\BCChange\\NewOptionalParameter',
            ], true)) {
                continue;
            }

            $arguments = $attribute->getArguments();
            $parameter = $arguments['parameterName'] ?? $arguments[1] ?? null;
            if (is_string($parameter) && $parameter !== '' && !in_array($parameter, $existingNames, true)) {
                $announcedNames[$parameter] = true;
            }
        }

        return $count <= count($parameters) + count($announcedNames);
    }
}
