<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rovereto\Metrika\Support\Facades\Metrika;

class MetrikaController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = Carbon::parse($request->get('startDate', Carbon::today()->subMonth()->addDay()->format('d.m.Y')));
        $endDate = Carbon::parse($request->get('endDate', Carbon::now()->format('d.m.Y')))->endOfDay();
        $group = $request->get('group', Metrika::getGroupDay());
        $groups = $groups_array = [];

        //Calculate dates by period and check grouping
        list($startDate, $endDate, $group, $groups) = Metrika::calculateDaysAndGroup($period, $startDate, $endDate, $group, $groups);

        $chart = [];
        //Visits
        $chart['hits'] = Metrika::getHitsForPeriodLine($startDate, $endDate, $group);

        $group_names = [
            'hour' => 'by hours',
            'day' => 'by days',
            'week' => 'by weeks',
            'month' => 'by months',
        ];

        foreach ($groups as $groups_item) {
            $groups_array[$groups_item] = $group_names[$groups_item];
        }

        //Popular Pages
        $pages = Metrika::getTopPageViewsForPeriod($startDate, $endDate, 25);
        //Sources
        $chart['sources'] = Metrika::getSourcesForPeriodPie($startDate, $endDate);
        //Search engines
        $chart['engine'] = Metrika::getSearchEngineForPeriodPie($startDate, $endDate);
        //Browsers
        $chart['browsers'] = Metrika::getBrowsersForPeriodPie($startDate, $endDate);
        //OS
        $chart['os'] = Metrika::getOsForPeriodPie($startDate, $endDate);
        //Devices
        $chart['devices'] = Metrika::getDevicesForPeriodPie($startDate, $endDate);
        //Countries
        $chart['countries'] = Metrika::getCountryForPeriodPie($startDate, $endDate);

        return view("metrika", compact('chart', 'pages', 'period', 'group', 'groups_array'), ['startDate' => $startDate->format('d.m.Y'), 'endDate' => $endDate->format('d.m.Y')]);
    }
}
