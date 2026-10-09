<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\PolyfillReplacementRule;

use Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\DynamicallyScheduledTaskHandler;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\NumberRange\ValueGenerator\AbstractNumberRangeValueGenerator;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;

abstract class LegacyProductStreamBuilder implements ProductStreamBuilderInterface {}

abstract class LegacyNumberRangeValueGenerator implements NumberRangeValueGeneratorInterface {}

abstract class LegacyScheduledTaskHandler extends ScheduledTaskHandler
{
    protected function rescheduleTask(ScheduledTask $task, ScheduledTaskEntity $taskEntity): void {}
}

abstract class CompatibleProductStreamBuilder extends AbstractProductStreamBuilder implements ProductStreamBuilderInterface {}

abstract class CompatibleNumberRangeValueGenerator extends AbstractNumberRangeValueGenerator implements NumberRangeValueGeneratorInterface {}

abstract class CompatibleScheduledTaskHandler extends ScheduledTaskHandler implements DynamicallyScheduledTaskHandler
{
    protected function rescheduleTask(ScheduledTask $task, ScheduledTaskEntity $taskEntity): void {}

    public function getNextExecutionTime(ScheduledTask $task, ScheduledTaskEntity $taskEntity): ?\DateTimeInterface
    {
        return null;
    }
}

final class ProductStreamBuilderConsumer
{
    public function __construct(
        private readonly AbstractProductStreamBuilder|ProductStreamBuilderInterface $builder,
    ) {}
}
