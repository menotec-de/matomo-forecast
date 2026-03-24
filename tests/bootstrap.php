<?php

declare(strict_types=1);

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
