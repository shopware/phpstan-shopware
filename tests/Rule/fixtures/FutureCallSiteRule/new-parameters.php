<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\FutureCallSiteRule\NewParameters;

use Shopware\Core\Framework\Deprecation\BCChange\NewOptionalParameter;
use Shopware\Core\Framework\Deprecation\BCChange\NewRequiredParameter;

class Subject
{
    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'localeCode', parameterType: 'string')]
    public function required(): void
    {
        $localeCode = \func_num_args() === 1 ? \func_get_arg(0) : 'en_GB';
    }

    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'states', parameterType: 'list<string>', defaultValue: [])]
    public function optional(string $scope): void
    {
        $states = \func_get_args()[1] ?? [];
    }

    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'localeCode', parameterType: 'string')]
    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'strict', parameterType: 'bool')]
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'states', parameterType: 'list<string>', defaultValue: [])]
    public function multiple(int $id): void
    {
        $arguments = \func_get_args();
    }

    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'other', parameterType: '?string')]
    public static function nullable(): void
    {
        $other = \func_get_args()[0] ?? null;
    }

    #[NewRequiredParameter(version: 'v6.8.0', parameterName: 'context', parameterType: 'MissingClass')]
    public function unresolved(): void
    {
        $context = \func_get_args()[0] ?? null;
    }
}

class ConstructorSubject
{
    #[NewOptionalParameter(version: 'v6.8.0', parameterName: 'strict', parameterType: 'bool', defaultValue: false)]
    public function __construct()
    {
        $strict = \func_get_args()[0] ?? false;
    }
}

class Caller
{
    public function check(Subject $subject, int|string $localeCode, array $arguments): void
    {
        $subject->required();
        $subject->required('en_GB');
        $subject->required(123);
        $subject->required(null);
        $subject->required($localeCode);
        $subject->optional('scope');
        $subject->optional('scope', []);
        $subject->optional('scope', ['state']);
        $subject->optional('scope', 123);
        $subject->optional('scope', [123]);
        $subject->multiple(1, 'en_GB');
        $subject->multiple(1, 'en_GB', true);
        $subject->multiple(1, 'en_GB', true, ['state']);
        $subject->multiple(1, 'en_GB', 'wrong', [123]);
        Subject::nullable();
        Subject::nullable(null);
        Subject::nullable('value');
        Subject::nullable(123);
        $subject->unresolved(123);
        $subject->required(...$arguments);
        $subject->optional('scope', ...$arguments);
        new ConstructorSubject();
        new ConstructorSubject(true);
        new ConstructorSubject('wrong');
    }

    /** @deprecated tag:v6.8.0 - This caller will be removed. */
    public function removed(Subject $subject): void
    {
        $subject->required(123);
        $subject->optional('scope', 123);
    }
}
