<?php

namespace Rovereto\Metrika\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array calculateDaysAndGroup(string $period, DateTime $startDate, DateTime $endDate, string $group, array $groups_array)
 * @method static array calculateIntervals(string $group, DateTime $startDate, DateTime $endDate)
 * @method static string getGroupDay()
 *
 * @method static array getHitsForPeriodLine(DateTime $startDate, DateTime $endDate, string $group, bool $with_robots)
 * @method static array getTopPageViewsForPeriod(DateTime $startDate, DateTime $endDate, int $limit, bool $with_robots)
 * @method static array getSourcesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 * @method static array getSearchEngineForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 * @method static array getBrowsersForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 * @method static array getOsForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 * @method static array getDevicesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 * @method static array getCountryForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots)
 *
 * @method static array getPie()
 *
 * @see \Rovereto\Metrika\Metrika
 */
class Metrika extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Metrika';
    }
}
