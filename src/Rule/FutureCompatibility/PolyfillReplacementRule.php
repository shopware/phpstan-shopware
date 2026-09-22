<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Rule\FutureCompatibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\DynamicallyScheduledTaskHandler;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\NumberRange\ValueGenerator\AbstractNumberRangeValueGenerator;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;

/**
 * @implements Rule<InClassNode>
 * @internal
 */
final class PolyfillReplacementRule implements Rule
{
    private const POLYFILL_MARKER = 'Keulinho\\ShopwarePolyfill\\ClassAliasLoader';

    private const ABSTRACT_CLASS_REPLACEMENTS = [
        ProductStreamBuilderInterface::class => AbstractProductStreamBuilder::class,
        NumberRangeValueGeneratorInterface::class => AbstractNumberRangeValueGenerator::class,
    ];

    private readonly bool $polyfillInstalled;

    public function __construct(?bool $polyfillInstalled = null)
    {
        $this->polyfillInstalled = $polyfillInstalled ?? class_exists(self::POLYFILL_MARKER);
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->polyfillInstalled) {
            return [];
        }

        $class = $node->getClassReflection();
        $originalNode = $node->getOriginalNode();
        if (!$originalNode instanceof Class_) {
            return [];
        }

        $errors = $this->abstractClassReplacementErrors($originalNode, $class, $scope);

        $rescheduleTask = $originalNode->getMethod('rescheduleTask');
        if ($rescheduleTask !== null
            && $class->isSubclassOf(ScheduledTaskHandler::class)
            && !$this->implements($class, DynamicallyScheduledTaskHandler::class)
        ) {
            $errors[] = RuleErrorBuilder::message(sprintf(
                'Class "%s" overrides deprecated "%s::rescheduleTask()". The installed keulinho/shopware-polyfill package provides "%s" on older Shopware versions; implement it and getNextExecutionTime() now, while keeping rescheduleTask() for older versions.',
                $class->getDisplayName(),
                ScheduledTaskHandler::class,
                DynamicallyScheduledTaskHandler::class,
            ))
                ->identifier('shopware.futureIncompatibility.polyfillDynamicScheduledTask')
                ->line($rescheduleTask->getStartLine())
                ->build();
        }

        return $errors;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function abstractClassReplacementErrors(Class_ $node, ClassReflection $class, Scope $scope): array
    {
        $errors = [];

        foreach ($node->implements as $interface) {
            $interfaceName = $scope->resolveName($interface);
            $replacement = self::ABSTRACT_CLASS_REPLACEMENTS[$interfaceName] ?? null;
            if ($replacement === null || $class->isSubclassOf($replacement)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Class "%s" implements deprecated "%s". The installed keulinho/shopware-polyfill package provides "%s" on older Shopware versions; extend it instead to use the replacement API now.',
                $class->getDisplayName(),
                $interfaceName,
                $replacement,
            ))
                ->identifier('shopware.futureIncompatibility.polyfillAbstractClass')
                ->line($interface->getStartLine())
                ->build();
        }

        return $errors;
    }

    /** @param class-string $interface */
    private function implements(ClassReflection $class, string $interface): bool
    {
        foreach ($class->getInterfaces() as $implementedInterface) {
            if ($implementedInterface->getName() === $interface) {
                return true;
            }
        }

        return false;
    }
}
