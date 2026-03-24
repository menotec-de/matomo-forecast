<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Repositories\ForecastRepository;

class ForecastRepositoryTest extends TestCase
{
    public function testPersistMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastRepository::class, 'persist');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(2, $params);
        self::assertSame('resultForDatabase', $params[0]->getName());
        self::assertSame('string', $params[0]->getType()->getName());
        self::assertSame('siteId', $params[1]->getName());
        self::assertSame('int', $params[1]->getType()->getName());
    }

    public function testFetchBySiteIdMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastRepository::class, 'fetchBySiteId');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(1, $params);
        self::assertSame('siteId', $params[0]->getName());
        self::assertSame('int', $params[0]->getType()->getName());
    }

    public function testFetchBySiteIdReturnsString(): void
    {
        $method     = new \ReflectionMethod(ForecastRepository::class, 'fetchBySiteId');
        $returnType = $method->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame('string', $returnType->getName());
    }

    public function testPersistReturnsVoid(): void
    {
        $method     = new \ReflectionMethod(ForecastRepository::class, 'persist');
        $returnType = $method->getReturnType();

        self::assertNotNull($returnType);
        self::assertSame('void', $returnType->getName());
    }
}
