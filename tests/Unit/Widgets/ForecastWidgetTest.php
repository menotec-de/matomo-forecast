<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Widgets;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Widgets\ForecastWidget;
use Piwik\Widget\WidgetConfig;

class ForecastWidgetTest extends TestCase
{
    public function testConfigureSetsExpectedCategoryAndName(): void
    {
        $config = $this->createMock(WidgetConfig::class);

        $config->expects(self::once())
            ->method('setCategoryId')
            ->with('General_Visitors');

        $config->expects(self::once())
            ->method('setName')
            ->with('Forecast');

        ForecastWidget::configure($config);
    }
}
