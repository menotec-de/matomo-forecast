<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands\Repositories;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\Repositories\VisitRepository;

class VisitRepositoryTest extends TestCase
{
    public function testFetchDaysMethodSignature(): void
    {
        $method = new \ReflectionMethod(VisitRepository::class, 'fetchDays');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(3, $params);
        self::assertSame('startDate', $params[0]->getName());
        self::assertSame('string', $params[0]->getType()->getName());
        self::assertSame('endDate', $params[1]->getName());
        self::assertSame('string', $params[1]->getType()->getName());
        self::assertSame('siteId', $params[2]->getName());
        self::assertSame('int', $params[2]->getType()->getName());
    }

    public function testSiteIdHasDefaultValue(): void
    {
        $method = new \ReflectionMethod(VisitRepository::class, 'fetchDays');
        $siteIdParam = $method->getParameters()[2];

        self::assertTrue($siteIdParam->isDefaultValueAvailable());
        self::assertSame(1, $siteIdParam->getDefaultValue());
    }

    public function testFetchDaysReturnsArray(): void
    {
        $method = new \ReflectionMethod(VisitRepository::class, 'fetchDays');
        $returnType = $method->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame('array', $returnType->getName());
    }
}
