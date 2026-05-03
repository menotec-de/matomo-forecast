<?php

declare(strict_types=1);

use function DI\factory;
use DI\ContainerBuilder;
use Piwik\Container\Container;
use Piwik\Container\ContainerDoesNotExistException;
use Piwik\Container\StaticContainer;
use Piwik\Translation\Loader\JsonFileLoader;
use Piwik\Translation\Translator;

$pluginAutoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($pluginAutoloader)) {
    fwrite(STDERR, "Run 'composer install' in the Forecast plugin directory first.\n");
    exit(1);
}
require_once $pluginAutoloader;

$matomoAutoloader = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($matomoAutoloader)) {
    require_once $matomoAutoloader;
}

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(__DIR__ . '/../../..') ?: __DIR__ . '/../../..');
}

// Minimal container so Piwik::translate() works in unit tests (Translator needs Config::getInstance()).
if (class_exists(StaticContainer::class)) {
    try {
        StaticContainer::getContainer();
    } catch (ContainerDoesNotExistException $e) {
        $pluginLangDir = dirname(__DIR__) . '/lang';

        $builder = new ContainerBuilder(Container::class);
        $builder->useAnnotations(false);
        $builder->addDefinitions([
            'Piwik\Config' => factory(static function () {
                return new class () extends \Piwik\Config {
                    /** @var array<string, mixed> */
                    public $General = ['default_language' => 'en'];

                    public function __construct()
                    {
                    }
                };
            }),
            'Piwik\Translation\Translator' => factory(static function () use ($pluginLangDir) {
                $loader = new JsonFileLoader();
                $translator = new Translator($loader, [$pluginLangDir]);
                $translator->setCurrentLanguage('en');

                return $translator;
            }),
        ]);

        /** @var Container $container */
        $container = $builder->build();
        StaticContainer::push($container);
    }
}
