<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable;
use Piwik\Plugins\Forecast\Controller;
use Piwik\Plugins\Forecast\Repositories\ForecastRepository;
use Piwik\Request;

class ControllerTest extends TestCase
{
    /** @var Controller */
    private $controller;

    /** @var ForecastRepository */
    private $repository;

    protected function setUp(): void
    {
        $reflection       = new \ReflectionClass(Controller::class);
        $this->controller = $reflection->newInstanceWithoutConstructor();

        $this->repository = $this->createMock(ForecastRepository::class);

        $repoProperty = $reflection->getProperty('forecastRepository');
        $repoProperty->setAccessible(true);
        $repoProperty->setValue($this->controller, $this->repository);
    }

    // ─── getData ────────────────────────────────────────────────────

    public function testGetDataReturnsEmptyTableWhenNoData(): void
    {
        $this->repository->method('fetchBySiteId')->willReturn('');

        $result = $this->controller->getData('2026-12-31', 1);

        self::assertInstanceOf(DataTable::class, $result);
        self::assertSame(0, $result->getRowsCount());
    }

    public function testGetDataFiltersRowsByDate(): void
    {
        $forecastData = json_encode([
            '2026-01-01' => ['label' => '2026-01-01', 'nb_uniq_visitors' => 10],
            '2026-01-02' => ['label' => '2026-01-02', 'nb_uniq_visitors' => 20],
            '2026-01-03' => ['label' => '2026-01-03', 'nb_uniq_visitors' => 30],
        ]);

        $this->repository->method('fetchBySiteId')->willReturn($forecastData);

        $result = $this->controller->getData('2026-01-02', 1);

        self::assertSame(2, $result->getRowsCount());
    }

    public function testGetDataReturnsAllRowsWhenDateIsFarFuture(): void
    {
        $forecastData = json_encode([
            '2026-01-01' => ['label' => '2026-01-01', 'nb_uniq_visitors' => 10],
            '2026-01-02' => ['label' => '2026-01-02', 'nb_uniq_visitors' => 20],
        ]);

        $this->repository->method('fetchBySiteId')->willReturn($forecastData);

        $result = $this->controller->getData('2099-12-31', 1);

        self::assertSame(2, $result->getRowsCount());
    }

    public function testGetDataRowsContainExpectedColumns(): void
    {
        $forecastData = json_encode([
            '2026-01-01' => ['label' => '2026-01-01', 'nb_uniq_visitors' => 42],
        ]);

        $this->repository->method('fetchBySiteId')->willReturn($forecastData);

        $result = $this->controller->getData('2026-12-31', 1);
        $row    = $result->getFirstRow();

        self::assertSame('2026-01-01', $row->getColumn('label'));
        self::assertSame(42, $row->getColumn('nb_uniq_visitors'));
    }

    public function testGetDataPassesSiteIdToRepository(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('fetchBySiteId')
            ->with(42)
            ->willReturn('');

        $this->controller->getData('2026-12-31', 42);
    }

    // ─── calculateDateTill ──────────────────────────────────────────

    public function testCalculateDateTillDayPeriod(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getParameter')
            ->willReturnMap([
                ['evolution_day_last_n', 8, 10],
            ]);

        $method = new \ReflectionMethod(Controller::class, 'calculateDateTill');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, 'day', $request);

        $expected = (new \DateTime())->modify('+10 days');

        self::assertSame($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function testCalculateDateTillMonthPeriod(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getParameter')
            ->willReturnMap([
                ['evolution_month_last_n', 3, 6],
            ]);

        $method = new \ReflectionMethod(Controller::class, 'calculateDateTill');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, 'month', $request);

        $expected = (new \DateTime())->modify('+6 months');

        self::assertSame($expected->format('Y-m'), $result->format('Y-m'));
    }

    public function testCalculateDateTillDayPeriodUsesDefaultValue(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getParameter')
            ->willReturnMap([
                ['evolution_day_last_n', 8, 8],
            ]);

        $method = new \ReflectionMethod(Controller::class, 'calculateDateTill');
        $method->setAccessible(true);
        $result = $method->invoke($this->controller, 'day', $request);

        $expected = (new \DateTime())->modify('+8 days');

        self::assertSame($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function testCalculateDateTillThrowsForUnsupportedPeriod(): void
    {
        $request = $this->createMock(Request::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported period "week"');

        $method = new \ReflectionMethod(Controller::class, 'calculateDateTill');
        $method->setAccessible(true);
        $method->invoke($this->controller, 'week', $request);
    }
}
