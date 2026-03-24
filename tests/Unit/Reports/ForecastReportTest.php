<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Reports;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Reports\ForecastReport;

class ForecastReportTest extends TestCase
{
    public function testClassExtendsReport(): void
    {
        $reflection = new \ReflectionClass(ForecastReport::class);

        self::assertTrue($reflection->isSubclassOf(\Piwik\Plugin\Report::class));
    }

    public function testInitSetsExpectedProperties(): void
    {
        $reflection = new \ReflectionClass(ForecastReport::class);
        $report     = $reflection->newInstanceWithoutConstructor();

        $initMethod = $reflection->getMethod('init');
        $initMethod->setAccessible(true);
        $initMethod->invoke($report);

        $props = ['name', 'categoryId', 'subcategoryId', 'module', 'action', 'order'];
        foreach ($props as $propName) {
            $prop = $reflection->getProperty($propName);
            $prop->setAccessible(true);
        }

        $name          = $reflection->getProperty('name');
        $name->setAccessible(true);
        $categoryId    = $reflection->getProperty('categoryId');
        $categoryId->setAccessible(true);
        $subcategoryId = $reflection->getProperty('subcategoryId');
        $subcategoryId->setAccessible(true);
        $module        = $reflection->getProperty('module');
        $module->setAccessible(true);
        $action        = $reflection->getProperty('action');
        $action->setAccessible(true);
        $order         = $reflection->getProperty('order');
        $order->setAccessible(true);

        self::assertSame('Forecast Report', $name->getValue($report));
        self::assertSame('Forecast', $categoryId->getValue($report));
        self::assertSame('General_Overview', $subcategoryId->getValue($report));
        self::assertSame('Forecast', $module->getValue($report));
        self::assertSame('getForecastReport', $action->getValue($report));
        self::assertSame(10, $order->getValue($report));
    }

    public function testConfigureViewMethodIsPublic(): void
    {
        $method = new \ReflectionMethod(ForecastReport::class, 'configureView');

        self::assertTrue($method->isPublic());
        self::assertSame(1, $method->getNumberOfParameters());
    }
}
