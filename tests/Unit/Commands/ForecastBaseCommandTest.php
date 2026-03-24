<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\ForecastBaseCommand;

/**
 * Concrete stub that exposes the protected methods of the abstract ForecastBaseCommand.
 */
class TestableForecastBaseCommand extends ForecastBaseCommand
{
    protected function doExecute(): int
    {
        return 0;
    }

    public function callFormatVisitsForProphet(array $visits, string $startDate, string $endDate): string
    {
        return $this->formatVisitsForProphet($visits, $startDate, $endDate);
    }

    public function callFormatProphetResultForDatabase(array $prophetResults): string
    {
        return $this->formatProphetResultForDatabase($prophetResults);
    }
}

class ForecastBaseCommandTest extends TestCase
{
    /** @var TestableForecastBaseCommand */
    private $command;

    protected function setUp(): void
    {
        $this->command = new TestableForecastBaseCommand();
    }

    // ─── formatVisitsForProphet ──────────────────────────────────────

    public function testFormatVisitsReturnsValidJson(): void
    {
        $visits = [
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => 5, 'total_visits' => 10],
        ];

        $json = $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-01');

        self::assertJson($json);
    }

    public function testFormatVisitsMapsFieldsCorrectly(): void
    {
        $visits = [
            '2026-03-01' => ['date' => '2026-03-01', 'unique_visits' => 42, 'total_visits' => 99],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-03-01', '2026-03-01'),
            true
        );

        self::assertCount(1, $result);
        self::assertSame(['ds', 'y'], array_keys($result[0]));
        self::assertSame('2026-03-01', $result[0]['ds']);
        self::assertSame(42, $result[0]['y']);
    }

    public function testFormatVisitsFillsMissingDatesWithZero(): void
    {
        $visits = [
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => 10, 'total_visits' => 15],
            '2026-01-03' => ['date' => '2026-01-03', 'unique_visits' => 30, 'total_visits' => 35],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-03'),
            true
        );

        self::assertCount(3, $result);
        self::assertSame('2026-01-02', $result[1]['ds']);
        self::assertSame(0, $result[1]['y']);
    }

    public function testFormatVisitsPreservesInputKeys(): void
    {
        $visits = [
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => 10, 'total_visits' => 15],
            '2026-01-02' => ['date' => '2026-01-02', 'unique_visits' => 20, 'total_visits' => 25],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-02'),
            true
        );

        self::assertCount(2, $result);
        self::assertSame('2026-01-01', $result[0]['ds']);
        self::assertSame('2026-01-02', $result[1]['ds']);
    }

    public function testFormatVisitsHandlesStringValuesFromDatabase(): void
    {
        $visits = [
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => '99', 'total_visits' => '100'],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-01'),
            true
        );

        self::assertEquals(99, $result[0]['y']);
    }

    public function testFormatVisitsSortsChronologically(): void
    {
        $visits = [
            '2026-01-03' => ['date' => '2026-01-03', 'unique_visits' => 30, 'total_visits' => 35],
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => 10, 'total_visits' => 15],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-03'),
            true
        );

        $dates = array_column($result, 'ds');
        self::assertSame(['2026-01-01', '2026-01-02', '2026-01-03'], $dates);
    }

    public function testFormatVisitsEmptyArrayProducesAllZeros(): void
    {
        $result = json_decode(
            $this->command->callFormatVisitsForProphet([], '2026-01-01', '2026-01-03'),
            true
        );

        self::assertCount(3, $result);
        foreach ($result as $row) {
            self::assertSame(0, $row['y']);
        }
    }

    public function testFormatVisitsOutputContainsOnlyDsAndY(): void
    {
        $visits = [
            '2026-01-01' => ['date' => '2026-01-01', 'unique_visits' => 10, 'total_visits' => 15, 'extra' => 'ignored'],
        ];

        $result = json_decode(
            $this->command->callFormatVisitsForProphet($visits, '2026-01-01', '2026-01-01'),
            true
        );

        self::assertSame(['ds', 'y'], array_keys($result[0]));
    }

    // ─── formatProphetResultForDatabase ──────────────────────────────

    public function testFormatResultReturnsValidJson(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => 10.0, 'yhat_lower' => 5, 'yhat_upper' => 15],
        ];

        self::assertJson($this->command->callFormatProphetResultForDatabase($input));
    }

    public function testFormatResultStructure(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => 42.3, 'yhat_lower' => 30, 'yhat_upper' => 55],
        ];

        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase($input),
            true
        );

        self::assertArrayHasKey('2026-02-01', $result);
        self::assertSame('2026-02-01', $result['2026-02-01']['label']);
        self::assertArrayHasKey('nb_uniq_visitors', $result['2026-02-01']);
    }

    public function testFormatResultClampsNegativeToZero(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => -5.7, 'yhat_lower' => -10, 'yhat_upper' => 0],
        ];

        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase($input),
            true
        );

        self::assertSame(0, $result['2026-02-01']['nb_uniq_visitors']);
    }

    public function testFormatResultRoundsHalfUp(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => 42.5, 'yhat_lower' => 30, 'yhat_upper' => 55],
            ['ds' => '2026-02-02', 'yhat' => 42.4, 'yhat_lower' => 30, 'yhat_upper' => 55],
            ['ds' => '2026-02-03', 'yhat' => 42.6, 'yhat_lower' => 30, 'yhat_upper' => 55],
        ];

        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase($input),
            true
        );

        self::assertEquals(43, $result['2026-02-01']['nb_uniq_visitors']);
        self::assertEquals(42, $result['2026-02-02']['nb_uniq_visitors']);
        self::assertEquals(43, $result['2026-02-03']['nb_uniq_visitors']);
    }

    public function testFormatResultMultipleRows(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => 10.0, 'yhat_lower' => 5, 'yhat_upper' => 15],
            ['ds' => '2026-02-02', 'yhat' => 20.0, 'yhat_lower' => 15, 'yhat_upper' => 25],
            ['ds' => '2026-02-03', 'yhat' => 30.0, 'yhat_lower' => 25, 'yhat_upper' => 35],
        ];

        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase($input),
            true
        );

        self::assertCount(3, $result);
        self::assertEquals(10, $result['2026-02-01']['nb_uniq_visitors']);
        self::assertEquals(20, $result['2026-02-02']['nb_uniq_visitors']);
        self::assertEquals(30, $result['2026-02-03']['nb_uniq_visitors']);
    }

    public function testFormatResultEmptyInputReturnsEmptyJson(): void
    {
        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase([]),
            true
        );

        self::assertEmpty($result);
    }

    public function testFormatResultZeroYhatRemainsZero(): void
    {
        $input = [
            ['ds' => '2026-02-01', 'yhat' => 0.0, 'yhat_lower' => -5, 'yhat_upper' => 5],
        ];

        $result = json_decode(
            $this->command->callFormatProphetResultForDatabase($input),
            true
        );

        self::assertEquals(0, $result['2026-02-01']['nb_uniq_visitors']);
    }

    // ─── FORECAST_DAYS constant ─────────────────────────────────────

    public function testForecastDaysConstant(): void
    {
        self::assertSame(365, ForecastBaseCommand::FORECAST_DAYS);
    }
}
