<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Forecast;

class ForecastPluginTest extends TestCase
{
    /** @var Forecast */
    private $plugin;

    protected function setUp(): void
    {
        $reflection   = new \ReflectionClass(Forecast::class);
        $this->plugin = $reflection->newInstanceWithoutConstructor();
    }

    public function testRegisterEventsReturnsExpectedArray(): void
    {
        $events = $this->plugin->registerEvents();

        self::assertIsArray($events);
        self::assertArrayHasKey('AssetManager.getJavaScriptFiles', $events);
        self::assertSame('getJavaScriptFiles', $events['AssetManager.getJavaScriptFiles']);
    }

    public function testGetJavaScriptFilesAppendsCorrectPath(): void
    {
        $files = [];
        $this->plugin->getJavaScriptFiles($files);

        self::assertCount(1, $files);
        self::assertSame('plugins/Forecast/javascripts/customLimits.js', $files[0]);
    }

    public function testGetJavaScriptFilesAppendsToExistingArray(): void
    {
        $files = ['plugins/CoreHome/javascripts/some.js'];
        $this->plugin->getJavaScriptFiles($files);

        self::assertCount(2, $files);
        self::assertSame('plugins/Forecast/javascripts/customLimits.js', $files[1]);
    }
}
