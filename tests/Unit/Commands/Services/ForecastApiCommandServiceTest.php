<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit\Commands\Services;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\Commands\Services\ForecastApiCommandService;
use RuntimeException;

class ForecastApiCommandServiceTest extends TestCase
{
    /** @var ForecastApiCommandService */
    private $service;

    /** @var \ReflectionClass */
    private $reflection;

    protected function setUp(): void
    {
        $this->reflection = new \ReflectionClass(ForecastApiCommandService::class);
        $this->service    = $this->reflection->newInstanceWithoutConstructor();

        foreach (['apiHostname', 'apiHostnameHost', 'apiKey', 'logger'] as $propName) {
            $this->reflection->getProperty($propName)->setAccessible(true);
        }

        $this->reflection->getProperty('apiHostname')->setValue($this->service, 'https://api.example.com');
        $this->reflection->getProperty('apiHostnameHost')->setValue($this->service, 'api.example.com');
        $this->reflection->getProperty('apiKey')->setValue($this->service, 'test-api-key');

        $logger = $this->createMock(\Piwik\Log\LoggerInterface::class);
        $this->reflection->getProperty('logger')->setValue($this->service, $logger);
    }

    // ─── persist: JSON payload validation ────────────────────────────

    public function testPersistThrowsOnEmptyJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON payload');

        $this->service->persist('', 1);
    }

    public function testPersistThrowsOnInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON payload');

        $this->service->persist('{invalid', 1);
    }

    public function testPersistThrowsOnPlainString(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON payload');

        $this->service->persist('hello world', 1);
    }

    // ─── persist: hostname validation ────────────────────────────────

    public function testPersistThrowsOnInvalidHostname(): void
    {
        $prop = $this->reflection->getProperty('apiHostname');
        $prop->setAccessible(true);
        $prop->setValue($this->service, 'not-a-valid-url');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid hostname');

        $this->service->persist('[]', 1);
    }

    // ─── method signatures ────────────────────────────────────────────

    public function testFetchMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastApiCommandService::class, 'fetch');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(1, $params);
        self::assertSame('siteId', $params[0]->getName());
        self::assertSame('int', $params[0]->getType()->getName());
    }

    public function testPersistMethodSignature(): void
    {
        $method = new \ReflectionMethod(ForecastApiCommandService::class, 'persist');

        self::assertTrue($method->isPublic());

        $params = $method->getParameters();
        self::assertCount(2, $params);
        self::assertSame('visitsJson', $params[0]->getName());
        self::assertSame('siteId', $params[1]->getName());
    }

    // ─── constants ────────────────────────────────────────────────────

    public function testTimeoutConstantsAreDefined(): void
    {
        $reflection = new \ReflectionClass(ForecastApiCommandService::class);

        self::assertSame(50, $reflection->getConstant('CURL_TIMEOUT_SECONDS'));
        self::assertSame(20, $reflection->getConstant('CURL_CONNECT_TIMEOUT_SEC'));
        self::assertSame(200, $reflection->getConstant('HTTP_OK_MIN'));
        self::assertSame(299, $reflection->getConstant('HTTP_OK_MAX'));
    }
}
