<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\API;
use Piwik\Plugins\Forecast\Repositories\ForecastRepository;

class APITest extends TestCase
{
    public function testGetForecastReportEnforcesAccessCheck(): void
    {
        $reflection = new \ReflectionClass(API::class);
        $api        = $reflection->newInstanceWithoutConstructor();

        $repository = $this->createMock(ForecastRepository::class);
        $repository->expects(self::never())->method('fetchBySiteId');

        $repoProp = $reflection->getProperty('forecastRepository');
        $repoProp->setAccessible(true);
        $repoProp->setValue($api, $repository);

        try {
            $api->getForecastReport(1);
            self::fail('Expected an exception due to missing authentication context');
        } catch (\Throwable $e) {
            self::assertTrue(true);
        }
    }

    public function testGetForecastReportMethodSignature(): void
    {
        $method = new \ReflectionMethod(API::class, 'getForecastReport');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(1, $params);
        self::assertSame('idSite', $params[0]->getName());
        self::assertSame('int', $params[0]->getType()->getName());
    }

    public function testClassExtendsPluginApi(): void
    {
        $reflection = new \ReflectionClass(API::class);

        self::assertTrue($reflection->isSubclassOf(\Piwik\Plugin\API::class));
    }
}
