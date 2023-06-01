<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Laravel Metrika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@easepick/datetime@1.2.0/dist/index.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@easepick/core@1.2.0/dist/index.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@easepick/base-plugin@1.2.0/dist/index.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@easepick/range-plugin@1.2.0/dist/index.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@easepick/amp-plugin@1.2.0/dist/index.umd.min.js"></script>
</head>

<body>
<div class="container">
    <h1>Laravel Metrika</h1>
    <div class="row">
        <div class="col-auto mb-2">
            <button type="button" class="btn btn-sm period @if($period == 'today') btn-primary @else btn-light @endif"
                    data-period="today">Today
            </button>
            <button type="button"
                    class="btn btn-sm period @if($period == 'yesterday') btn-primary @else btn-light @endif"
                    data-period="yesterday">Yesterday
            </button>
            <button type="button" class="btn btn-sm period @if($period == 'week') btn-primary @else btn-light @endif"
                    data-period="week">Week
            </button>
            <button type="button" class="btn btn-sm period @if($period == 'month') btn-primary @else btn-light @endif"
                    data-period="month">Month
            </button>
            <button type="button" class="btn btn-sm period @if($period == 'quarter') btn-primary @else btn-light @endif"
                    data-period="quarter">Quarter
            </button>
            <button type="button" class="btn btn-sm period @if($period == 'year') btn-primary @else btn-light @endif"
                    data-period="year">Year
            </button>
            <button type="button" id="rangeDate"
                    class="btn btn-sm @if($period == 'range') btn-primary @else btn-light @endif"
                    value="{{ $startDate }} - {{ $endDate }}">{{ $startDate }} - {{ $endDate }}</button>
        </div>
        <div class="col-auto mb-2">
            <select id="group" name="group" class="form-control btn-light">
                @foreach($groups_array as $group_key => $group_name)
                    <option value="{{ $group_key }}"
                            @if($group == $group_key) selected @endif>{{ $group_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <section class="mb-4">
        <div class="row">
            <div class="col-xl-6 col-12 mb-4">
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="hits" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="sources" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="engine" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="browsers" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="os" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="devices" height="200"></div>
                        </div>
                    </div>
                </section>
                <section class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="w-100" id="countries" height="200"></div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-xl-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                            <tr>
                                <th scope="col">Locale</th>
                                <th scope="col">Host</th>
                                <th scope="col">Page</th>
                                <th scope="col">Views</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pages as $page)
                                <tr>
                                    <td>{{ $page['locale'] }}</td>
                                    <td>{{ $page['host'] }}</td>
                                    <td scope="row">{{ Str::limit($page['path'], 50) }}</td>
                                    <th>{{ $page['hits'] }}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var period = '{{ $period }}';
    var group = '{{ $group }}';
    var start = '{{ $startDate }}';
    var end = '{{ $endDate }}';

    function jump() {
        let url = '{{ route('metrika') }}';
        url += '?period=' + period;
        url += '&group=' + group;
        if (period == 'range') {
            url += start ? '&startDate=' + start : '';
            url += end ? '&endDate=' + end : '';
        }
        window.location = url;
    }

    var ButtonsPeriod = document.querySelectorAll('.period');
    ButtonsPeriod.forEach(function (element) {
        element.addEventListener('click', function (event) {
            period = element.getAttribute('data-period');
            jump();
        });
    });
    document.getElementById('group').onchange = function (event) {
        group = event.target.value;
        jump();
    };
    document.addEventListener('DOMContentLoaded', function(){
        const picker = new easepick.create({
            element: document.getElementById('rangeDate'),
            css: [
                'https://cdn.jsdelivr.net/npm/@easepick/core@1.2.0/dist/index.css',
                'https://cdn.jsdelivr.net/npm/@easepick/range-plugin@1.2.0/dist/index.css'
            ],
            zIndex: 10,
            format: 'DD.MM.YYYY',
            grid: 3,
            calendars: 3,
            readonly: false,
            autoApply: false,
            AmpPlugin: {
                dropdown: {
                    months: true,
                    years: true
                },
                darkMode: false
            },
            plugins: ['AmpPlugin', 'RangePlugin'],

            setup(picker) {
                picker.on('select', (e) => {
                    start = e.detail.start.format('DD.MM.YYYY');
                    end = e.detail.end.format('DD.MM.YYYY');
                    period = 'range';
                    let str = start + ' - ' + end;
                    jump();
                });
            },
        });
        picker.setDateRange('{{ $startDate }}', '{{ $endDate }}');
        picker.gotoDate('{{ $endDate }}');

        Highcharts.chart('hits', {
            chart: {
                type: 'line'
            },
            title: {
                text: 'Visitors',
                align: 'low',
                textAlign: 'left'
            },
            xAxis: {
                categories: {!! $chart['hits']['dateArray'] !!},
                minTickInterval: 3
            },
            legend: {
                align: 'left'
            },
            yAxis: {
                title: {
                    enabled: false
                }
            },
            series: {!!  $chart['hits']['dataArray'] !!}
        });

        Highcharts.chart('sources', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Sources'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'Sources',
                colorByPoint: true,
                data: {!!  $chart['sources']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['sources']['drilldownArray'] !!}
            }
        });

        Highcharts.chart('engine', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Search engines'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'Search engines',
                colorByPoint: true,
                data: {!!  $chart['engine']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['engine']['drilldownArray'] !!}
            }
        });

        Highcharts.chart('browsers', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Browsers'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'Browsers',
                colorByPoint: true,
                data: {!!  $chart['browsers']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['browsers']['drilldownArray'] !!}
            }
        });

        Highcharts.chart('os', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'OS'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'OS',
                colorByPoint: true,
                data: {!!  $chart['os']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['os']['drilldownArray'] !!}
            }
        });

        Highcharts.chart('devices', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Devices'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'Devices',
                colorByPoint: true,
                data: {!!  $chart['devices']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['devices']['drilldownArray'] !!}
            }
        });

        Highcharts.chart('countries', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Countries'
            },
            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.percentage:.2f}% ({point.y})</b>'
            },
            accessibility: {
                announceNewData: {
                    enabled: true
                },
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                series: {
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f}% ({point.y})'
                    }
                }
            },
            series: [{
                name: 'Countries',
                colorByPoint: true,
                data: {!!  $chart['countries']['dataArray'] !!}
            }],
            drilldown: {
                series: {!!  $chart['countries']['drilldownArray'] !!}
            }
        });
    });
</script>
</body>
</html>