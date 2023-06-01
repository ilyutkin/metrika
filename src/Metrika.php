<?php

declare(strict_types=1);

namespace Rovereto\Metrika;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Support\Facades\DB;
use Rovereto\Metrika\Responses\MetrikaResponse;
use Rovereto\Metrika\Responses\Types\MetrikaRequestData;
use Rovereto\Metrika\Responses\Types\MetrikaRequestQuery;

class Metrika
{
    const PERIOD_TODAY = 'today';
    const PERIOD_YESTERDAY = 'yesterday';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_QUARTER = 'quarter';
    const PERIOD_YEAR = 'year';
    const PERIOD_RANGE = 'range';

    const GROUP_HOUR = 'hour';
    const GROUP_DAY = 'day';
    const GROUP_WEEK = 'week';
    const GROUP_MONTH = 'month';

    protected $default_period = self::PERIOD_MONTH;

    protected $periods = [
        self::PERIOD_TODAY,
        self::PERIOD_YESTERDAY,
        self::PERIOD_WEEK,
        self::PERIOD_MONTH,
        self::PERIOD_QUARTER,
        self::PERIOD_YEAR,
        self::PERIOD_RANGE
    ];

    protected $default_group = self::GROUP_DAY;

    protected $groups = [
        self::GROUP_HOUR,
        self::GROUP_DAY,
        self::GROUP_WEEK,
        self::GROUP_MONTH
    ];

    /**
     * Graph hits by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param string $group
     * @param bool $with_robots
     * @return array
     */
    public function getHitsForPeriodLine(DateTime $startDate, DateTime $endDate,
                                           string $group = 'day', bool $with_robots = false)
    {
        //Calculating intervals
        list($data, $dates, $date_position) = $this->calculateIntervals($group, $startDate, $endDate);

        switch ($group) {
            case self::GROUP_HOUR:
                $sql_format = "%d.%m:%H";
                $groupBy = 'HOUR(created_at)';
                $orderBy = 'HOUR(created_at)';
                break;
            case self::GROUP_WEEK:
                $sql_format = "%v.%x";
                $groupBy = 'WEEK(created_at, 1)';
                $orderBy = 'WEEK(created_at, 1)';
                break;
            case self::GROUP_MONTH:
                $sql_format = "%m.%Y";
                $groupBy = 'DATE_FORMAT(created_at, \'%Y-%m\')';
                $orderBy = 'DATE_FORMAT(created_at, \'%Y-%m\')';
                break;
            default:
                $sql_format = "%d.%m";
                $groupBy = 'DATE(created_at)';
                $orderBy = 'DATE(created_at)';
        }

        $builder = app('metrika.hit')::addSelect([
            'visitor_id',
            DB::raw("DATE_FORMAT(created_at, '$sql_format') as date"),
            DB::raw("DATE_FORMAT(created_at, '%i') as minute"),
            DB::raw('COUNT(id) as hits'),
            DB::raw('COUNT(DISTINCT visit_id) as visits'),
            DB::raw('COUNT(DISTINCT visitor_id) as visitors')
        ])
            ->whereRaw('DATE(created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupByRaw($groupBy)
            ->orderByRaw($orderBy);

        if (!$with_robots) {
            $builder->whereHas('visitor', function ($query) use ($startDate, $endDate) {
                return $query->where('is_robot', 0);
            });
        }

        $itemArray = [
            'hits' => $data,
            'visits' => $data,
            'visitors' => $data,
        ];

        foreach ($builder->get() as $item) {
            $itemArray['hits'][$date_position[$item->date]]['y'] = $item->hits;
            $itemArray['visits'][$date_position[$item->date]]['y'] = $item->visits;
            $itemArray['visitors'][$date_position[$item->date]]['y'] = $item->visitors;
        }

        $dataArray = [
            ['name' => trans('metrika::messages.hits'), 'data' => array_values($itemArray['hits'])],
            ['name' => trans('metrika::messages.visits'), 'data' => array_values($itemArray['visits'])],
            ['name' => trans('metrika::messages.visitors'), 'data' => array_values($itemArray['visitors'])],
        ];

        return [
            'dataArray' => json_encode($dataArray, JSON_UNESCAPED_UNICODE),
            'dateArray' => json_encode($dates, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Most viewed pages for the selected period, count - $limit
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $limit
     * @return array
     */
    public function getTopPageViewsForPeriod(DateTime $startDate, DateTime $endDate, int $limit = 10, bool $with_robots = false): array
    {
        $builder = app('metrika.hit')::with('path')
            ->addSelect([
                'path_id',
                DB::raw('COUNT(id) as hits')
            ])
            ->whereRaw('DATE(created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupBy('path_id')
            ->orderByDesc('hits')
            ->limit($limit);

        if (!$with_robots) {
            $builder->whereHas('visitor', function ($query) use ($startDate, $endDate) {
                return $query->where('is_robot', 0);
            });
        }

        $dataArray = [];

        foreach ($builder->get() as $item) {
            $dataArray[] = [
                'host' => $item->path->host,
                'path' => $item->path->path,
                'locale' => $item->path->locale,
                'hits' => $item->hits,
            ];
        }

        return $dataArray;
    }

    /**
     * Graph pie source by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getSourcesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("COALESCE(medium, 'direct') as dimension"),
            DB::raw("source as sub_dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits')
        ])
            ->leftJoin(config('metrika.tables.referers'),
                config('metrika.tables.visits') . '.referer_id', '=', config('metrika.tables.referers') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupBy('medium')
            ->groupBy('source')
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->whereHas('visitor', function ($query) use ($startDate, $endDate) {
                return $query->where('is_robot', 0);
            });
        }

        return $this->getPie($builder->get());
    }

    /**
     * Graph pie search system by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getSearchEngineForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("source as dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits')
        ])
            ->leftJoin(config('metrika.tables.referers'),
                config('metrika.tables.visits') . '.referer_id', '=', config('metrika.tables.referers') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->where('medium', 'search')
            ->groupBy('medium')
            ->groupBy('source')
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->whereHas('visitor', function ($query) use ($startDate, $endDate) {
                return $query->where('is_robot', 0);
            });
        }

        return $this->getPie($builder->get());
    }

    /**
     * Graph pie browsers by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getBrowsersForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("COALESCE(family, 'unknown') as dimension"),
            DB::raw("COALESCE(NULLIF(IF(POSITION('.' IN version), SUBSTR(version, 1, POSITION('.' IN version) - 1), version), ''), 'unknown') as sub_dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits')
        ])
            ->leftJoin(config('metrika.tables.visitors'),
                config('metrika.tables.visits') . '.visitor_id', '=', config('metrika.tables.visitors') . '.id')
            ->leftJoin(config('metrika.tables.agents'),
                config('metrika.tables.visitors') . '.agent_id', '=', config('metrika.tables.agents') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupByRaw("COALESCE(family, 'unknown')")
            ->groupByRaw("COALESCE(SUBSTR(version, 1, POSITION('.' IN version) - 1), 'unknown')")
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->where(config('metrika.tables.visitors') . '.is_robot', 0);
        }

        return $this->getPie($builder->get());
    }

    /**
     * Graph pie operating systems by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getOsForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("COALESCE(family, 'unknown') as dimension"),
            DB::raw("COALESCE(NULLIF(IF(POSITION('.' IN version), SUBSTR(version, 1, POSITION('.' IN version) - 1), version), ''), 'unknown') as sub_dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits')
        ])
            ->leftJoin(config('metrika.tables.visitors'),
                config('metrika.tables.visits') . '.visitor_id', '=', config('metrika.tables.visitors') . '.id')
            ->leftJoin(config('metrika.tables.platforms'),
                config('metrika.tables.visitors') . '.platform_id', '=', config('metrika.tables.platforms') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupBy('family')
            ->groupByRaw("COALESCE(NULLIF(IF(POSITION('.' IN version), SUBSTR(version, 1, POSITION('.' IN version) - 1), version), ''), 'unknown')")
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->where(config('metrika.tables.visitors') . '.is_robot', 0);
        }

        return $this->getPie($builder->get());
    }

    /**
     * Graph pie devices by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getDevicesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("COALESCE(brand, 'unknown') as dimension"),
            DB::raw("COALESCE(model, 'unknown') as sub_dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits'),
            DB::raw('COUNT(DISTINCT ' . config('metrika.tables.visits') . '.visitor_id) as visitors'),
            DB::raw('ROUND(SUM(IF(' . config('metrika.tables.visits') . '.count = 1, 1, 0)) * 100 / COUNT(' . config('metrika.tables.visits') . '.id), 2) as bounceRate'),
            DB::raw('ROUND(AVG(TIMESTAMPDIFF(SECOND, ' . config('metrika.tables.visits') . '.created_at, ' . config('metrika.tables.visits') . '.last_view_at)), 2) as avgVisitDurationSeconds')
        ])
            ->leftJoin(config('metrika.tables.visitors'),
                config('metrika.tables.visits') . '.visitor_id', '=', config('metrika.tables.visitors') . '.id')
            ->leftJoin(config('metrika.tables.devices'),
                config('metrika.tables.visitors') . '.device_id', '=', config('metrika.tables.devices') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupByRaw("COALESCE(brand, 'unknown')")
            ->groupByRaw("COALESCE(model, 'unknown')")
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->where(config('metrika.tables.visitors') . '.is_robot', 0);
        }

        return $this->getPie($builder->get());
    }

    /**
     * Graph pie countries and regions by period
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param bool $with_robots
     * @return array
     */
    public function getCountryForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false): array
    {
        $builder = app('metrika.visit')::select([
            DB::raw("COALESCE(country, 'unknown') as dimension"),
            DB::raw("COALESCE(subdivision, 'unknown') as sub_dimension"),
            DB::raw('COUNT(' . config('metrika.tables.visits') . '.id) as visits'),
        ])
            ->leftJoin(config('metrika.tables.visitors'),
                config('metrika.tables.visits') . '.visitor_id', '=', config('metrika.tables.visitors') . '.id')
            ->leftJoin(config('metrika.tables.geoips'),
                config('metrika.tables.visits') . '.geoip_id', '=', config('metrika.tables.geoips') . '.id')
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) >= ?', $startDate->format('Y-m-d'))
            ->whereRaw('DATE(' . config('metrika.tables.visits') . '.created_at) <= ?', $endDate->format('Y-m-d'))
            ->groupByRaw("COALESCE(country, 'unknown')")
            ->groupByRaw("COALESCE(subdivision, 'unknown')")
            ->orderByDesc('visits');

        if (!$with_robots) {
            $builder->where(config('metrika.tables.visitors') . '.is_robot', 0);
        }

        return $this->getPie($builder->get());
    }

    /**
     * Calculate dates by period and check grouping
     *
     * @param string $period
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param string $group
     * @param array $groups_array
     * @return array
     */
    public function calculateDaysAndGroup(string $period, DateTime $startDate, DateTime $endDate,
                                          string $group = self::GROUP_DAY, array $groups_array = [])
    {
        if (!in_array($period, $this->periods)) {
            $period = $this->default_period;
        }

        if (!in_array($group, $this->groups)) {
            $group = $this->default_group;
        }

        switch ($period) {
            case self::PERIOD_TODAY:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $groups_array = [self::GROUP_HOUR];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_HOUR;
                }
                break;
            case self::PERIOD_YESTERDAY:
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                $groups_array = [self::GROUP_HOUR];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_HOUR;
                }
                break;
            case self::PERIOD_WEEK:
                $startDate = Carbon::today()->subDays(6);
                $endDate = Carbon::now();
                $groups_array = [self::GROUP_HOUR, self::GROUP_DAY];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_DAY;
                }
                break;
            case self::PERIOD_QUARTER:
                $startDate = Carbon::today()->subMonths(3)->addDay();
                $endDate = Carbon::now();
                $groups_array = [self::GROUP_DAY, self::GROUP_WEEK, self::GROUP_MONTH];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_WEEK;
                }
                break;
            case self::PERIOD_YEAR:
                $startDate = Carbon::today()->subYear()->addDay();
                $endDate = Carbon::now();
                $groups_array = [self::GROUP_DAY, self::GROUP_WEEK, self::GROUP_MONTH];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_MONTH;
                }
                break;
            case self::PERIOD_RANGE:
                $diff = $startDate->diff($endDate);
                if ($diff->days == 0) {
                    $groups_array = [self::GROUP_HOUR];
                    if (!in_array($group, $groups_array)) {
                        $group = self::GROUP_HOUR;
                    }
                } elseif ($diff->days < 32) {
                    $groups_array = [self::GROUP_HOUR, self::GROUP_DAY, self::GROUP_WEEK];
                    if (!in_array($group, $groups_array)) {
                        $group = self::GROUP_DAY;
                    }
                } elseif ($diff->days < 95) {
                    $groups_array = [self::GROUP_DAY, self::GROUP_WEEK, self::GROUP_MONTH];
                    if (!in_array($group, $groups_array)) {
                        $group = self::GROUP_WEEK;
                    }
                } else {
                    $groups_array = [self::GROUP_DAY, self::GROUP_WEEK, self::GROUP_MONTH];
                    if (!in_array($group, $groups_array)) {
                        $group = self::GROUP_MONTH;
                    }
                }
                break;
            //Month
            default:
                $startDate = Carbon::today()->subMonth()->addDay();
                $endDate = Carbon::now();
                $groups_array = [self::GROUP_HOUR, self::GROUP_DAY, self::GROUP_WEEK];
                if (!in_array($group, $groups_array)) {
                    $group = self::GROUP_DAY;
                }
        }

        return [$startDate, $endDate, $group, $groups_array];
    }

    /**
     * Calculating intervals
     *
     * @param string $group
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return array[]
     */
    public function calculateIntervals(string $group, DateTime $startDate, DateTime $endDate)
    {
        switch ($group) {
            case self::GROUP_HOUR:
                $interval = CarbonInterval::hour();
                break;
            case self::GROUP_WEEK:
                $interval = CarbonInterval::week();
                break;
            case self::GROUP_MONTH:
                $interval = CarbonInterval::month();
                break;
            default:
                $interval = CarbonInterval::day();
        }

        $startDateForPeriod = $startDate->copy();
        if (($group == self::GROUP_MONTH) && ($startDateForPeriod->format('d') > 28)) {
            $startDateForPeriod->subWeek();
        }
        $periodDate = CarbonPeriod::create($startDateForPeriod, $endDate);
        $periodDate->setDateInterval($interval);

        $data = $dates = $date_position = [];
        $i = 0;
        $last_period = null;
        foreach ($periodDate as $date) {
            switch ($group) {
                case self::GROUP_HOUR:
                    $date_format_key = $date->format('d.m:H');
                    $date_format = $date->format('H:00');
                    $date_format_full = $date->translatedFormat('d.m H:00, l');
                    break;
                case self::GROUP_WEEK:
                    $date_format_key = $date->format('W.Y');
                    if ($date != $periodDate->startDate) {
                        $date->startOfWeek();
                    }
                    if ($periodDate->last() &&
                        ($endDate->copy()->startOfWeek()->format('d.m') == $date->copy()->startOfWeek()->format('d.m'))) {
                        $date_format = $date->format('d.m') . ' - ' . $endDate->format('d.m');
                        $date_format_full = $date->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y');
                    } else {
                        $date_format = $date->format('d.m') . ' - ' . $date->copy()->endOfWeek()->format('d.m');
                        $date_format_full = $date->format('d.m.Y') . ' - ' . $date->copy()->endOfWeek()->format('d.m.Y');
                    }
                    break;
                case self::GROUP_MONTH:
                    $date_format_key = $date->format('m.Y');
                    $date_format = $date->translatedFormat('M Y');
                    $date_format_full = $date->translatedFormat('F Y');
                    break;
                //Day
                default:
                    $date_format_key = $date->format('d.m');
                    $date_format = $date->format('d.m');
                    $date_format_full = $date->translatedFormat('d.m.Y, l');
            }
            $dates[] = $date_format;
            $data[$i] = [
                'x' => $i,
                'y' => null,
                'name' => $date_format_full
            ];
            $date_position[$date_format_key] = $i;
            $last_period = $date;
            $i++;
        }

        if (($group == 'week') &&
            ($endDate->copy()->startOfWeek()->format('d.m') != $last_period->startOfWeek()->format('d.m'))) {
            $dates[] = $endDate->copy()->startOfWeek()->format('d.m') . ' - ' . $endDate->format('d.m');
            $data[$i] = [
                'x' => $i,
                'y' => null,
                'name' => $endDate->copy()->startOfWeek()->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y')
            ];
            $date_position[$endDate->copy()->startOfWeek()->format('W.Y')] = $i;
        }
        if (($group == 'month') &&
            ($endDate->copy()->startOfWeek()->format('m.Y') != $last_period->startOfWeek()->format('m.Y'))) {
            $dates[] = $endDate->translatedFormat('M Y');
            $data[$i] = [
                'x' => $i,
                'y' => null,
                'name' => $endDate->translatedFormat('F Y')
            ];
            $date_position[$endDate->format('m.Y')] = $i;
        }

        return [$data, $dates, $date_position];
    }

    /**
     * Preparing data for a graph
     *
     * @param $data
     * @return array
     */
    public function getPie($data)
    {
        //Choose unique id countries/regions
        $key_array = [];

        //Result array with id and country/region name
        $idArray = [];
        $total = 0;

        foreach ($data as $value) {
            //Check if there is such a value in the array
            if (!in_array($value->dimension, $key_array)) {
                //If not, then we put it in the search array and in the resulting array
                $key_array[] = $value->dimension;
                $idArray[] = $value->dimension;
            }
            $total += $value->visits;
        }

        //Number of unique countries/areas
        $cnt = count($idArray);

        //Arrays for plotting
        $dataArray = [];        // countries/regions
        $drilldownArray = [];   // regions/cities

        for ($i = 0; $i < $cnt; $i++) {
            $dataArray[$i] = [
                'name' => $idArray[$i],
                'y' => 0,
                'drilldown' => null,
            ];
            $drilldownDataArray = [];

            //Iterate over the source array and select the desired data
            foreach ($data as $item) {
                //If country/region id matches
                if ($item->dimension == $idArray[$i]) {
                    //Add the number of visits to the general list of the country/region
                    $dataArray[$i]['y'] += $item->visits;

                    if (!empty($item->sub_dimension)) {
                        //If there is no name for the region / city
                        if ($item->sub_dimension) {
                            $region = $item->sub_dimension;
                        } else {
                            $region = trans('metrika::messages.undefined');
                        }
                        $drilldownDataArray[] = [
                            $region,
                            $item->visits,
                        ];
                    }
                }
            }
            if (count($drilldownDataArray) > 1) {
                $dataArray[$i]['drilldown'] = $idArray[$i];
                $drilldownArray[$i] = [
                    'name' => $idArray[$i],
                    'id' => $idArray[$i],
                    'data' => $drilldownDataArray,
                ];
            }
        }

        return [
            'dataArray' => json_encode($dataArray, JSON_UNESCAPED_UNICODE),
            'drilldownArray' => json_encode(array_values($drilldownArray), JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Returns an array of periods
     *
     * @return mixed|string[]
     */
    public function getPeriods()
    {
        return $this->periods;
    }

    /**
     * @return string
     */
    public function getGroupDay()
    {
        return self::GROUP_DAY;
    }
}