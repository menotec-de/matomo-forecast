<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Forecast;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin;

class Forecast extends Plugin
{
    /**
     * Returns a map of Matomo events to listener method names.
     *
     * @return array<string, string>
     */
    public function registerEvents(): array
    {
        return [
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
        ];
    }

    /**
     * Appends the plugin's JavaScript file to the asset list.
     *
     * @param array $files Reference to the list of JavaScript asset paths.
     * @return void
     */
    public function getJavaScriptFiles(array &$files): void
    {
        $files[] = 'plugins/Forecast/javascripts/customLimits.js';
    }

    /**
     * Called when the plugin is installed.
     *
     * @return void
     * @throws \Exception
     */
    public function install(): void
    {
        $this->createTables();
    }

    /**
     * Called when the plugin is uninstalled.
     *
     * @return void
     * @throws \Exception
     */
    public function uninstall(): void
    {
        $this->removeTables();
    }

    /**
     * Called when the plugin is activated.
     *
     * @return void
     * @throws \Exception
     */
    public function activate(): void
    {
        $this->createTables();
    }

    /**
     * Creates the forecast database table if it does not already exist.
     *
     * @return void
     * @throws \Exception
     */
    private function createTables(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . Common::prefixTable('forecast_access_count') . " (
            `access_siteid` INT UNSIGNED NOT NULL,
            `access_data` JSON DEFAULT NULL,
            PRIMARY KEY (`access_siteid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ";

        Db::exec($sql);
    }

    /**
     * Drops the forecast database table.
     *
     * @return void
     * @throws \Exception
     */
    private function removeTables(): void
    {
        $sql = "DROP TABLE IF EXISTS " . Common::prefixTable('forecast_access_count');
        Db::exec($sql);
    }
}
