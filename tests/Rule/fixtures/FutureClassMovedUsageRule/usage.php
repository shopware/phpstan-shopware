<?php

declare(strict_types=1);

namespace Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule;

use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Canonical\MovedSubject as CanonicalMovedSubject;
use Shopware\PhpStan\Tests\Fixture\FutureClassMovedUsageRule\Legacy\MovedSubject;

class Consumer
{
    public MovedSubject $property;

    public CanonicalMovedSubject $canonicalProperty;

    public function useAlias(MovedSubject $subject): MovedSubject
    {
        $subject = new MovedSubject();
        $class = MovedSubject::class;

        if ($subject instanceof MovedSubject) {
            MovedSubject::call();
            MovedSubject::$value;
        }

        return $subject;
    }
}

class SecondConsumer
{
    public function useAlias(MovedSubject $subject): void
    {
        MovedSubject::call();
    }
}
