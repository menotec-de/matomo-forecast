<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\Services\ForecastCliCommandService;

class ForecastCliCommandServiceTest extends TestCase
{
    /** @var ForecastCliCommandService */
    private $service;

    /** @var \ReflectionMethod */
    private $validate;

    protected function setUp(): void
    {
        $this->service = new ForecastCliCommandService();

        // Expose the private validate() method so we can test its logic in
        // isolation without needing the Matomo DI container (which retrain()
        // triggers by instantiating SystemSettings before calling validate()).
        $this->validate = new \ReflectionMethod(ForecastCliCommandService::class, 'validate');
        $this->validate->setAccessible(true);
    }

    /**
     * Helper that invokes the private validate() method with the given arguments.
     *
     * @param string $pythonBinPath
     * @param string $modelDir
     * @param string $visitsJson
     * @param int    $siteId
     * @return void
     */
    private function callValidate(
        string $pythonBinPath,
        string $modelDir,
        string $visitsJson,
        int    $siteId
    ): void {
        $this->validate->invoke(
            $this->service,
            $pythonBinPath,
            $modelDir,
            $visitsJson,
            $siteId
        );
    }

    // ─── JSON validation ─────────────────────────────────────────────

    public function testValidateThrowsOnInvalidJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid visits JSON');

        $this->callValidate('/usr/bin/python3', '/tmp', 'not-valid-json', 1);
    }

    public function testValidateThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid visits JSON');

        $this->callValidate('/usr/bin/python3', '/tmp', '', 1);
    }

    // ─── siteId validation ───────────────────────────────────────────

    public function testValidateThrowsOnZeroSiteId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid siteId');

        $this->callValidate('/usr/bin/python3', '/tmp', '[]', 0);
    }

    public function testValidateThrowsOnNegativeSiteId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid siteId');

        $this->callValidate('/usr/bin/python3', '/tmp', '[]', -1);
    }

    // ─── Python binary validation ────────────────────────────────────

    public function testValidateThrowsOnEmptyPythonBinPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Python binary is not configured or not executable');

        $this->callValidate('', '/tmp', '[]', 1);
    }

    public function testValidateThrowsOnNonExecutablePython(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Python binary is not configured or not executable');

        $this->callValidate('/nonexistent/path/to/python', '/tmp', '[]', 1);
    }

    // ─── model dir helper ────────────────────────────────────────────

    public function testGetModelDirReturnsExpectedPath(): void
    {
        $method = new \ReflectionMethod(ForecastCliCommandService::class, 'getModelDir');
        $method->setAccessible(true);
        $result = $method->invoke($this->service);

        self::assertIsString($result);
        self::assertStringContainsString('tmp', $result);
        self::assertStringContainsString('forecast', $result);
    }

    // ─── method signatures ───────────────────────────────────────────

    public function testInferenceMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastCliCommandService::class, 'inference');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(2, $params);
        self::assertSame('visitsJson', $params[0]->getName());
        self::assertSame('siteId', $params[1]->getName());
    }

    public function testRetrainMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastCliCommandService::class, 'retrain');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(2, $params);
        self::assertSame('visitsJson', $params[0]->getName());
        self::assertSame('siteId', $params[1]->getName());
    }
}
