<?php

declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Forecast;

use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Exception\DI\DependencyException;
use Piwik\Exception\DI\NotFoundException;
use Piwik\Piwik;
use Piwik\Plugins\Forecast\Repositories\ForecastRepository;

class API extends \Piwik\Plugin\API
{
    /** @var ForecastRepository */
    private $forecastRepository;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct()
    {
        $this->forecastRepository = StaticContainer::get(ForecastRepository::class);
    }

    /**
     * Returns the forecast report for a given site, period, and date.
     *
     * @param int    $idSite Matomo site ID.
     * @param string $period Report period (e.g. 'day', 'month').
     * @param string $date   Report date string (e.g. 'today', '2026-01-01').
     * @return DataTable
     * @throws \Exception
     */
    public function getForecastReport(int $idSite, string $period, string $date): DataTable
    {
        Piwik::checkUserHasViewAccess($idSite);

        $dataTable = new DataTable();

        $result = $this->forecastRepository->fetchBySiteId($idSite);
        if (empty($result)) {
            return $dataTable;
        }

        $decodedData = json_decode($result, true);

        foreach ($decodedData as $decodedDataRow) {
            $dataTable->addRowFromArray([
                Row::COLUMNS => [
                    'label'     => $decodedDataRow['label'],
                    'nb_visits' => $decodedDataRow['nb_uniq_visitors'],
                ],
            ]);
        }

        return $dataTable;
    }
}
