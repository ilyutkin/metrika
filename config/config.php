<?php

declare(strict_types=1);

return [

    /*
     * If you prefer to store Metrika data on a different database or connection,
     * you can set it here.
     */
    'connection' => 'mysql',

    // Statistics Database Tables
    'tables' => [
        'hits' => 'metrika_hits',
        'data' => 'metrika_data',
        'paths' => 'metrika_paths',
        'geoips' => 'metrika_geoips',
        'routes' => 'metrika_routes',
        'agents' => 'metrika_agents',
        'visits' => 'metrika_visits',
        'devices' => 'metrika_devices',
        'domains' => 'metrika_domains',
        'queries' => 'metrika_queries',
        'referers' => 'metrika_referers',
        'visitors' => 'metrika_visitors',
        'platforms' => 'metrika_platforms',
    ],

    // Statistics Models
    'models' => [
        'hit' => \Rovereto\Metrika\Models\Hit::class,
        'path' => \Rovereto\Metrika\Models\Path::class,
        'datum' => \Rovereto\Metrika\Models\Datum::class,
        'geoip' => \Rovereto\Metrika\Models\Geoip::class,
        'route' => \Rovereto\Metrika\Models\Route::class,
        'agent' => \Rovereto\Metrika\Models\Agent::class,
        'query' => \Rovereto\Metrika\Models\Query::class,
        'visit' => \Rovereto\Metrika\Models\Visit::class,
        'device' => \Rovereto\Metrika\Models\Device::class,
        'domain' => \Rovereto\Metrika\Models\Domain::class,
        'referer' => \Rovereto\Metrika\Models\Referer::class,
        'visitor' => \Rovereto\Metrika\Models\Visitor::class,
        'platform' => \Rovereto\Metrika\Models\Platform::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics Crunching Lottery
    |--------------------------------------------------------------------------
    |
    | Raw statistical data needs to be crunched to extract meaningful stories.
    | Here the chances that it will happen on a given request. By default,
    | the odds are 2 out of 100. For better performance consider using
    | task scheduling and set this lottery option to "FALSE" then.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Statistics Cleaning Period
    |--------------------------------------------------------------------------
    |
    | If you would like to clean old statistics automatically, you may specify
    | the number of days after which the it will be wiped automatically.
    | Any records older than this period (in days) will be cleaned.
    |
    | Note that this cleaning process just affects `metrika_hits`
    | only! Other database tables are kept safely untouched anyway.
    |
    */

    'lifetime' => false,

    /*
    |--------------------------------------------------------------------------
    | Exclude input fields
    |--------------------------------------------------------------------------
    |
    | Some fields must/should/might be hidden. And you can decide which area
    | would be this is. For example sensitive informations, credit card info,
    | passwords, pins, secret keys and others. Also some fields are may not
    | important or may unecessary informations for statistics. For example csrf tokens,
    | nested inputs and other fields. This option excludes given form inputs.
    |
    | This option prevents possible data leaks because form inputs storing as raw.
    |
    */

    'exclude_input_fields' => [
        'email',
        'password',
        'password_confirmation',
        'secret',
        'secret_key',
        'pin',
        '_csrf',
        '_token',
        'card_number',
        'card_owner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Duration of visit
    |--------------------------------------------------------------------------
    |
    | The visit ends when no new events are received from the visitor for
    | a certain time - by default this is 30 minutes. If within 30 minutes from
    | the last event the same user visits the site again from an external source,
    | metrica will not consider such a visit as a new visit - all new page views
    | will be added to the previous visit.
    |
    | To make the duration of the visit infinite - write 0.
    |
    */

    'visit_close_time' => 30,

    /*
    |--------------------------------------------------------------------------
    | Enable cookie
    |--------------------------------------------------------------------------
    |
    | A cookie may be created on your visitor device, so you can have information
    | on everything made using that device on your site.
    |
    */

    'store_cookie' => false,

    /*
    |--------------------------------------------------------------------------
    | Cookie name
    |--------------------------------------------------------------------------
    |
    | If you are storing cookies, you better change it to a name you of your own.
    |
    */

    'cookie_name' => 'please_change_this_cookie_name',

    /*
    |--------------------------------------------------------------------------
    | Cookie lifetime
    |--------------------------------------------------------------------------
    |
    | If you are storing cookies, set the lifetime.
    |
    */

    'cookie_lifetime' => 525600,

];
