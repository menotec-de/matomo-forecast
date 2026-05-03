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
use Piwik\Request;
use Piwik\ViewDataTable\Factory;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Minimum number of forecast rows (after filtering by date) required before the chart
     * is rendered. Below this threshold the forecast is considered too sparse to be useful.
     */
    private const MIN_FORECAST_ROWS = 30;

    /**
     * Minimum share of forecast rows that must have a positive predicted visitor count
     * before the chart is rendered. When the predicted series is dominated by zeros
     * (e.g. a strong declining trend rounded to 0) the forecast is hidden instead of
     * shown as a near-flat zero line.
     */
    private const MIN_NON_ZERO_RATIO = 0.5;

    /** @var ForecastRepository */
    private $forecastRepository;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct()
    {
        parent::__construct();
        $this->forecastRepository = StaticContainer::get(ForecastRepository::class);
    }

    /**
     * Renders the forecast evolution graph widget.
     *
     * @return string
     * @throws \Exception
     */
    public function getRawData(): string
    {
        $request = Request::fromRequest();
        $idSite = (int)$request->getParameter('idSite', 1);

        Piwik::checkUserHasViewAccess($idSite);

        $period = $request->getParameter('period', 'day');
        $dateTill = $this->calculateDateTill($period, $request);

        $view = Factory::build('graphEvolution', 'Forecast.getRawData');
        $view->config->columns_to_display = ['nb_uniq_visitors'];
        $view->config->translations['nb_uniq_visitors'] = Piwik::translate('Forecast_ColumnUniqueVisitors');
        $view->config->enable_sort = false;
        $view->config->hide_annotations_view = true;
        // Replaces the default "No data for this period" message when the forecast is
        // hidden by Controller::isForecastUsable() (insufficient or zero-dominated data).
        $view->config->no_data_message = Piwik::translate('Forecast_InsufficientDataForCalculation');

        $view->setDataTable($this->getData($dateTill->format('Y-m-d'), $idSite));

        return $view->render();
    }

    /**
     * Builds a DataTable from stored forecast data filtered up to the given date.
     *
     * @param string $dateTill Upper-bound date in Y-m-d format.
     * @param int    $siteId   Matomo site ID.
     * @return DataTable
     * @throws \Exception
     */
    public function getData(string $dateTill, int $siteId): DataTable
    {
        $dataTable = new DataTable();

        $result = $this->forecastRepository->fetchBySiteId($siteId);
        if (empty($result)) {
            return $dataTable;
        }

        $filteredData = array_filter(
            json_decode($result, true),
            static function (string $date) use ($dateTill): bool {
                return $date <= $dateTill;
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!$this->isForecastUsable($filteredData)) {
            return $dataTable;
        }

        foreach ($filteredData as $filteredDataRow) {
            $dataTable->addRow(new Row([
                Row::COLUMNS => $filteredDataRow
            ]));
        }

        return $dataTable;
    }

    /**
     * Decides whether a filtered forecast slice is worth rendering.
     *
     * The chart is hidden when either the slice is too sparse (fewer than
     * MIN_FORECAST_ROWS rows) or when most rows predict zero visitors (less than
     * MIN_NON_ZERO_RATIO of the rows have nb_uniq_visitors > 0). The latter case
     * occurs when Prophet's trend extrapolates a declining series toward zero, which
     * after rounding produces a long string of zeros that is not useful to plot.
     *
     * @param array $filteredData Forecast rows (each having an 'nb_uniq_visitors' key).
     * @return bool True if the chart should be rendered.
     */
    private function isForecastUsable(array $filteredData): bool
    {
        $totalRows = count($filteredData);
        if ($totalRows < self::MIN_FORECAST_ROWS) {
            return false;
        }

        $nonZeroRows = count(array_filter(
            $filteredData,
            static function (array $row): bool {
                return ((float)($row['nb_uniq_visitors'] ?? 0)) > 0.0;
            }
        ));

        // Check if 50 percent is reached
        return ($nonZeroRows / $totalRows) >= self::MIN_NON_ZERO_RATIO;
    }

    /**
     * Calculates the upper-bound forecast date based on period and request params.
     *
     * @param string  $period  Supported: 'day', 'month'
     * @param Request $request Current HTTP request.
     * @return \DateTime
     * @throws \InvalidArgumentException When an unsupported period is given.
     * @throws \Exception
     */
    private function calculateDateTill(string $period, Request $request): \DateTime
    {
        $now = new \DateTime();

        switch ($period) {
            case 'day':
                return (clone $now)->modify(
                    sprintf('+%d days', (int)$request->getParameter('evolution_day_last_n', 8))
                );
            case 'month':
                return (clone $now)->modify(
                    sprintf('+%d months', (int)$request->getParameter('evolution_month_last_n', 3))
                );
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported period "%s". Allowed: day, month.', $period)
                );
        }
    }
}
