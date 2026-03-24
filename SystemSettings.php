<?php

declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Forecast;

use Piwik\Settings\FieldConfig;

class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var \Piwik\Settings\Plugin\SystemSetting */
    public $pythonBinPath;

    /** @var \Piwik\Settings\Plugin\SystemSetting */
    public $apiKey;

    /** @var \Piwik\Settings\Plugin\SystemSetting */
    public $apiHostname;

    /**
     * Initialises all plugin settings.
     *
     * @return void
     */
    protected function init(): void
    {
        $this->pythonBinPath = $this->createPythonBinPathSetting();
        $this->apiKey        = $this->createApiKeySettings();
        $this->apiHostname   = $this->createApiHostnameSettings();
    }

    /**
     * Creates the setting for the Python executable path.
     *
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createPythonBinPathSetting(): \Piwik\Settings\Plugin\SystemSetting
    {
        return $this->makeSetting('pythonBinPath', 'python3', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title       = 'Path to python bin';
            $field->uiControl   = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Set the path to the python executable';
        });
    }

    /**
     * Creates the setting for the remote API key.
     *
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createApiKeySettings(): \Piwik\Settings\Plugin\SystemSetting
    {
        return $this->makeSetting('apiKey', '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title       = 'API Key';
            $field->uiControl   = FieldConfig::UI_CONTROL_PASSWORD;
            $field->description = 'API key from menotec. Contact via info@menotec.de for an API key.';
        });
    }

    /**
     * Creates the setting for the remote API hostname.
     *
     * @return \Piwik\Settings\Plugin\SystemSetting
     */
    private function createApiHostnameSettings(): \Piwik\Settings\Plugin\SystemSetting
    {
        return $this->makeSetting('apiHostname', '', FieldConfig::TYPE_STRING, function (FieldConfig $field) {
            $field->title       = 'API hostname';
            $field->uiControl   = FieldConfig::UI_CONTROL_TEXT;
            $field->description = 'Enter the menotec hostname (e.g. http://forecast.localhost). Contact via info@menotec.de for hostname.';
        });
    }
}
