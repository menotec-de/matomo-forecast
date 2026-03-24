<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\ForecastBaseCommand;
use Piwik\Plugins\Forecast\Commands\ForecastRemotePersistCommand;

class ForecastRemotePersistCommandTest extends TestCase
{
    public function testClassExtendsForecastBaseCommand(): void
    {
        $reflection = new \ReflectionClass(ForecastRemotePersistCommand::class);

        self::assertTrue($reflection->isSubclassOf(ForecastBaseCommand::class));
    }

    public function testIsEnabledReturnsTrue(): void
    {
        $reflection = new \ReflectionClass(ForecastRemotePersistCommand::class);
        $command = $reflection->newInstanceWithoutConstructor();

        self::assertTrue($command->isEnabled());
    }

    public function testHasDoExecuteMethod(): void
    {
        $method = new \ReflectionMethod(ForecastRemotePersistCommand::class, 'doExecute');

        self::assertTrue($method->isProtected());
        self::assertSame('int', $method->getReturnType()->getName());
    }

    public function testConfigureMethodExists(): void
    {
        $method = new \ReflectionMethod(ForecastRemotePersistCommand::class, 'configure');

        self::assertTrue($method->isProtected());
    }
}
