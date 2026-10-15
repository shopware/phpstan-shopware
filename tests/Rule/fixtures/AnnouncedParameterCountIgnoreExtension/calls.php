<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\AnnouncedParameterCountIgnoreExtension;

use Shopware\Core\Framework\Deprecation\BCChange\NewOptionalParameter;
use Shopware\Core\Framework\Deprecation\BCChange\NewRequiredParameter;

abstract class AbstractRoute
{
    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'criteria', parameterType: 'string')]
    abstract public function get(int $id): void;
}

interface Route
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'criteria', parameterType: 'string', defaultValue: '')]
    public function get(int $id): void;
}

abstract class MultipleParameters
{
    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'criteria', parameterType: 'string')]
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'strict', parameterType: 'bool', defaultValue: false)]
    abstract public function get(int $id): void;
}

abstract class ExistingParameter
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'id', parameterType: 'int', defaultValue: 0)]
    abstract public function get(int $id = 0): void;
}

class Caller
{
    public function call(AbstractRoute $abstract, Route $interface, MultipleParameters $multiple, ExistingParameter $existing): void
    {
        $abstract->get(1, 'criteria');
        $interface->get(1, 'criteria');
        $interface->get(1);
        $multiple->get(1, 'criteria', true);
        $abstract->get();
        $abstract->get(1, 'criteria', true);
        $existing->get(1, 2);
        $abstract->get(id: 1, criteria: 'criteria');
        $abstract->get('wrong', 'criteria');
        $abstract->get(1, 123);
        $abstract->get(1);
        $interface->get(1, 123);
    }
}
