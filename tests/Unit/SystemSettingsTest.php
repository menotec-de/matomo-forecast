<?php

declare(strict_types=1);

namespace Piwik\Plugins\Forecast\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\Forecast\SystemSettings;

class SystemSettingsTest extends TestCase
{
    public function testClassExtendsPluginSystemSettings(): void
    {
        $reflection = new \ReflectionClass(SystemSettings::class);

        self::assertTrue(
            $reflection->isSubclassOf(\Piwik\Settings\Plugin\SystemSettings::class)
        );
    }

    public function testHasExpectedPublicProperties(): void
    {
        $reflection = new \ReflectionClass(SystemSettings::class);

        foreach (['pythonBinPath', 'apiKey', 'apiHostname'] as $property) {
            self::assertTrue($reflection->hasProperty($property), "Missing property: $property");
            self::assertTrue($reflection->getProperty($property)->isPublic(), "Property $property is not public");
        }
    }

    public function testInitMethodIsProtected(): void
    {
        $method = new \ReflectionMethod(SystemSettings::class, 'init');

        self::assertTrue($method->isProtected());
    }

    public function testHasPrivateSettingFactoryMethods(): void
    {
        $reflection = new \ReflectionClass(SystemSettings::class);

        foreach (['createPythonBinPathSetting', 'createApiKeySettings', 'createApiHostnameSettings'] as $method) {
            self::assertTrue($reflection->hasMethod($method), "Missing method: $method");
            self::assertTrue($reflection->getMethod($method)->isPrivate(), "Method $method is not private");
        }
    }
}
