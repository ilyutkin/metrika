# Laravel Metrika

This is a reworked copy of [Rinvex Statistics](https://github.com/rinvex/laravel-statistics) that was abandoned. 
Also inspired by the [Laravel Stats Tracker](https://github.com/antonioribeiro/tracker)

### Main differences
- Instead of the `requests` table, a bunch of `hits`, `visits` and `visitors` is used as in Yandex Metrica [Basic concepts: pageviews, sessions, users](https://yandex.com/adv/edu/metrika/metrika-start/basic-concepts-pageviews-sessions-users)
- Referrers are separated into their own table
- You can save statistics to another database
- Removed Rinvex dependencies

[![Packagist](https://img.shields.io/packagist/v/rovereto/metrika.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rovereto/metrika)
[![License](https://img.shields.io/packagist/l/rovereto/metrika?label=License&style=flat-square)](https://github.com/ilyutkin/metrika/blob/master/LICENSE.md)
[![Packagist Downloads](https://img.shields.io/packagist/dt/rovereto/metrika?style=flat-square)](https://packagist.org/packages/rovereto/metrika)

> **Description Rinvex Statistics**
>
> **Rinvex Statistics** is a lightweight, yet detailed package for tracking and recording user visits across your Laravel
> application. With only one simple query per request, important data is being stored, and later a cronjob crush numbers
> to extract meaningful stories from within the haystack.
>
> Unlike other tracking packages that seriously damage your project's performance (yes, I mean that package you know 😅),
> our package takes a different approach by just executing only one query at the end of each request after the response is
> being served to the user, through the `terminate` method of an automatically attached middleware, and then later on it
> uses the raw data previously inserted in the database to extract meaningfull numbers. This is done based on a random
> lottery request, or through a scheduled job (recommended) that could be queued to offload the heavy crunching work.
>
> **Rinvex Statistics** tracks each -valid- request, meaning only requests that goes through routing pipeline, which also
> means that any wrong URL that results in `NotFoundHttpException` will not be tracked. If requested page has uncaught
> exceptions, it won't be tracked as well. It track user's logged in account (if any), session of all users and guests (if
> any), device (family, model, brand), platform (family, version), browser (agent, kind, family, version), path, route (
> action, middleware, parameters), host, protocol, ip address, language, status codes, and many more, and still we've
> plenty of awesome features planned for the future.
>
> With such a huge collected data, the `statistics_requests` database table will noticeably increase in size specially if
> you've a lot of visits, that's why it's recommended to clean it periodically. Other important data will stay still in
> their respective tables, normalized and without any performance issues, so only this table need to be cleaned. By
> default that will be done automatically every month.
>
> The default implementation of **Rinvex Statistics** comes with zero configuration out-of-the-box, which means it just
> works once installed. But it's recommended to change the defaults and disable the "Statistics Crunching Lottery" from
> config file, and replace it with a [Scheduled Tasks](https://laravel.com/docs/master/scheduling) for even better
> performance if you've large number of visits. See [Usage](#usage) for details.

## Installation

1. Install the package via composer:
    ```shell
    composer require rovereto/metrika
    ```

2. Publish resources (migrations and config files):
    ```shell
    php artisan vendor:publish --provider="Rovereto\Metrika\Providers\MetrikaServiceProvider"
    ```

3. If you need to save statistics to another database, edit `config/metrika.php`
   ```php
   'connection' => 'metrika',
   ```
   And create a database connection for it on your `config/database.php`
   ```php
   'connections' => [
       'mysql' => [
           ...
       ],
       
       'metrika' => [
           'driver'   => '...',
           'host'     => '...',
           'database' => ...,
           'strict' => false,    // to avoid problems on some MySQL installs
           ...
       ],
   ],
   ```

4. Execute migrations via the following command:
    ```shell
    php artisan migrate
    ```
5. Done!

## Usage

**Laravel Metrika** like **Rinvex Statistics** has no usage instructions, because it just works! You install it and you
are done! Seriously!!

Anyway, as a recommended performance tweak go ahead and do the following (optionally):

1. Publish config file via the following command:
    ```
    php artisan vendor:publish --provider="Rovereto\Metrika\Providers\MetrikaServiceProvider"
    ```

2. Disable the "Statistics Crunching Lottery" from config file.

3. Follow the default Laravel documentation about [Scheduled Tasks](https://laravel.com/docs/master/scheduling), then
   schedule both `\Rovereto\Metrika\Jobs\CrunchStatistics` and `\Rovereto\Metrika\Jobs\CleanStatisticsRequests` jobs at
   whatever intervals you see appropriate.

4. Enjoy!

> **Note:** **Laravel Metrika** has a `\Rovereto\Metrika\Http\Middleware\TrackStatistics` middleware that attach 
> itself automatically to the `web` middleware group, that's how it works out-of-the-box with zero configuration.

### Data retrieval

You may need to build your own frontend interface to browse statistics, and for that you can utilize any of the included
eloquent models as you normally do with [Laravel Eloquent](https://laravel.com/docs/master/eloquent).

All eloquent models are self explainatory:

- `\Rovereto\Metrika\Models\Agent` browser agent model
- `\Rovereto\Metrika\Models\Datum` raw statistics data (to be crunched)
- `\Rovereto\Metrika\Models\Device` user device model
- `\Rovereto\Metrika\Models\Domain` referer device model
- `\Rovereto\Metrika\Models\Geoip` geo ip model
- `\Rovereto\Metrika\Models\Hit` user hit model
- `\Rovereto\Metrika\Models\Path` request path model
- `\Rovereto\Metrika\Models\Platform` user platform model
- `\Rovereto\Metrika\Models\Referer` request referer details model
- `\Rovereto\Metrika\Models\Route` request route details model
- `\Rovereto\Metrika\Models\Visit` visit model
- `\Rovereto\Metrika\Models\Visitor` visitor model

All models are bound to the [Service Container](https://laravel.com/docs/master/container) so you can swap easily from
anywhere in your application. In addition to the default normal way of using these models explicitely, you can use their
respective service names as in the following example:

```php
// Find first browser agent (any of these methods are valid and equivalent)
app('metrika.agent')->first();
new \Rovereto\Metrika\Models\Agent::first();
app(\Rovereto\Metrika\Models\Agent::class)->first();
```

Same for all other eloquent models.

### Identify Geographical Location

To determine the geographic location by ip address, the [GeoIP for Laravel (Torann\GeoIP)](https://github.com/Torann/laravel-geoip) 
package is used.

To customize the package, publish the configuration file:
   ```shell
   php artisan vendor:publish --provider="Torann\GeoIP\GeoIPServiceProvider" --tag=config
   ```
A configuration file will be publish to `config/geoip.php`.

An example of using MaxMindDatabase:
   ```php
   ...
   'service' => 'maxmind_database',
   ...
   'services' => [
   ...
        'maxmind_database' => [
            'class' => \Torann\GeoIP\Services\MaxMindDatabase::class,
            'database_path' => database_path('geoip/GeoLite2-City.mmdb'),
            'update_url' => sprintf('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz', env('MAXMIND_LICENSE_KEY')),
            'locales' => ['en'],
        ],
   ...
   ```

The Torann\GeoIP package does not support IP2Location, but they have implemented support for this base.

An example of using IP2Location:
   ```php
   ...
   'service' => 'ip2location_database',
   ...
   'services' => [
   ...
        'ip2location_database' => [
            'class' => \Rovereto\Metrika\Services\Ip2Location::class,
            'database_path' => database_path('ip2location/IP2LOCATION.BIN'),
        ],
   ...
   ```

### Identify Proxy

To determine the proxy by ip address, the [IP2Proxy PHP Module](https://github.com/ip2location/ip2proxy-php)
package is used.

Detection the proxy are disabled by default. Enable them in the config file `config/metrika.php`, 
also write the filename of the bin database.

An example of using determine the proxy:
   ```php
   ...
   'use_proxy' => true,
   ...
   'proxy_path' => database_path('ip2location/IP2PROXY.BIN'),
   ...
   ```

### Using cookie files

Metrika may use anonymous identifiers to keep track of visitors, which are stored in a cookie. 
Cookies are disabled by default. Enable them in the config file `config/metrika.php`, also give the cookie a name.
An example:

   ```php
   ...
   'store_cookie' => true,
   ...
   'cookie_name' => 'my_name_cookie_for_metrika',
   ...
   ```

And set the first level domain for the cookie in the file `.env`:

   ```
   SESSION_DOMAIN=".my-domain.com"
   ```

or file `config/session.php`:

   ```php
   ...
   'domain' => env('SESSION_DOMAIN', '.my-domain.com'),
   ...
   ```

If you are using multiple subdomains, to identify the same user, disable cookie encryption for the Metrika in the file
`app/Http/Middleware/EncryptCookies.php`:

   ```php
   ...
   protected $except = [
   ...
       'my_name_cookie_for_metrika'
   ...  
   ];
   ...
   ```

> **Notes:**
> Each Laravel application encrypts cookies with its own key. Therefore, the cookies of one caller will be encrypted 
> in different ways.


### Data usage

Two class connection options Laravel Metrika
```php
use Rovereto\Metrika\Support\Facades\Metrika;
```
or
```php
use Metrika;
```

#### Most viewed pages for the selected period, count - $limit

```php
use Rovereto\Metrika\Support\Facades\Metrika;

Metrika::getTopPageViewsForPeriod(DateTime $startDate, DateTime $endDate, int $limit = 10, bool $with_robots = false);
//example Most viewed pages for period 01.01.2020 - 31.12.2020 limit 100 pages without robots
Metrika::getTopPageViewsForPeriod(Carbon::parse('01.01.2020'), Carbon::parse('31.12.2020'), 100);
```

#### Graph number of: hits, visits, unique visitors by day
Return an array for plotting a line graph

```php
use Rovereto\Metrika\Support\Facades\Metrika;

Metrika::getHitsForPeriodLine(DateTime $startDate, DateTime $endDate, string $group = 'day', bool $with_robots = false);
//example Hits for period 01.01.2020 - 31.12.2020 by month without robots
Metrika::getHitsForPeriodLine(Carbon::parse('01.01.2020'), Carbon::parse('31.12.2020'), 'month');
```

#### Methods for building pie charts

##### Graph pie source by period

```php
Metrika::getSourcesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

##### Graph pie search system by period

```php
Metrika::getSearchEngineForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

##### Graph pie browsers by period

```php
Metrika::getBrowsersForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

##### Graph pie operating systems by period

```php
Metrika::getOsForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

##### Graph pie devices by period

```php
Metrika::getDevicesForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

##### Graph pie countries and regions by period

```php
Metrika::getCountryForPeriodPie(DateTime $startDate, DateTime $endDate, bool $with_robots = false);
```

> An example of usage is in the directory `examples`

### Counts that matters

All agent, device, path, platform, route models have a `count` attribute, which gets updated automatically whenever a
new request has been tracked.

This `count` attribute reflects number of hits. To make it clear let's explain through data samples:

#### Agents

| id | name | kind | family | version | count |
| --- | --- | --- | --- | --- | --- |
| 1 | Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36 | desktop | Chrome | 63.0.3239 | 734 | 

This means there's 734 visit to our project through **Chrome** browser, version **63.0.3239**, with agent (**
Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84
Safari/537.36**)

#### Devices

| id | family | model | brand | count |
| --- | --- | --- | --- | --- |
| 1 | iPhone | iPhone | Apple | 83 | 

This means there's 83 visits to our project through **iPhone** device.

#### Platforms

| id | family | version | count |
| --- | --- | --- | --- |
| 1 | Mac OS X | 10.12.6 | 615 |

This means there's 615 visits to our project through **Mac OS X** operating system, with version **10.12.6**.

#### Paths

| id | host | locale | path | count |
| --- | --- | --- | --- | --- |
| 1 | test.homestead.local | en | en/adminarea/roles/admin | 12 |

This means there's 12 visits to the admin dashboard roles management of the **test.homestead.local** host (in case you
have multiple hosts or wildcard subdomains enabled on the same project, you can track all of them correctly here). The
english interface was used, one for locale (english in this case), and updated role record (admin in this case).

This table could be used as a visit counter for all your pages. To retrieve and display page views you can use the
following code for example:

```php
$pageViews = app('metrika.path')->where('path', request()->decodedPath())->first()->count;
```

And simply use the `$pageViews` variable anywhere in your views or controllers, or anywhere else. That way you have
automatic visit counter for all your project's pages, very useful and performant, ready at your fingertips. You can
add `host` contraint in case you have wildcard subdomains enabled.

#### Queries

| id | query |
| --- | --- |
| 1 | {"name":"Mike","city":"New York"} |

Request parameters as json

#### Routes

| id | name | path | action | middleware | parameters | count |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | adminarea.roles.edit | {locale}/adminarea/roles/{role} | App\Http\Controllers\Adminarea\RolesController@form | ["web","nohttpcache","can:access-adminarea","auth","can:update-roles,roles"] | {"role": "[a-z0-9-]+", "locale": "[a-z]{2}"} | 41 |

This means there's 41 visits to the `adminarea.roles.edit` route, which has the `{locale}/adminarea/roles/{role}` raw
path, and served through the `App\Http\Controllers\Adminarea\RolesController@form` controller action, and has the
following middleware applied `["web","nohttpcache","can:access-adminarea","auth","can:update-roles,roles"]`, knowing the
route accepts two parameters with the following regex requirements `{"role": "[a-z0-9-]+", "locale": "[a-z]{2}"}`.

As you can see, this `statistics_routes` table beside the `statistics_paths` table are both complimentary, and could be
used together to track which paths and routs are being accessed, how many times, and what controller actions serve it,
and what parameters are required, with the actual parameter replacements used to access it. Think of routes as your raw
links blueprint map, and of paths as the executed and actually used links by users.

#### Referers


| id | domain_id | url | medium | source |  count | 
| --- | --- | --- | --- | --- | --- |
| 1 | 3 | https://www.facebook.com/ | social | Facebook |  57 |

This means there's 57 visits to our project from the social facebook.com.

Medium field options:
- search - from a search engine
- social - from social networks
- unknown - referrer unknown
- internal - internal referrer
- email - switch from email
- invalid - referrer detection error

#### Domains

| id | name | count |
| --- | --- | --- |
| 1 | google.com | 24    | 

This means there's 24 visits to our project from google.com.

#### Geoips

| id | client_ip | latitude | longitude | country_code | client_ips | is_from_trusted_proxy | division_code | postal_code | timezone | city | count |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 127.0.0.0 | 41.31 | -72.92 | US | NULL | 0 | CT | 06510 | America/New_York | New Haven | 57 |

This means there's 57 visits to the project from IP address `127.0.0.0` with the latitude, longitude and timezone
mentioned above coming from `New Haven` city, `Connecticut` state.

#### Visitors

| id | user_type | user_id | cookie_id | agent_id | device_id | platform_id | language | is_robot | count  | created_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | user | 123 | ttivveoa18qc471vv9qfbg6qr1 | 123 | 123 | 123 | en_US | 0 | 55     | 2022-05-07 09:42:39 | 

Unique visitors. Through `session_id`, `user_id` and `user_type` you can track guests (logged out) and users (logged in).

#### Visits

| id | visitor_id | user_type | user_id | session_id | geoip_id | referer_id | count | created_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 123 | user | 123 | 5lajaqs5g4em8vqaoub4hbicf0 | 123 | 123 | 2 | 2022-05-07 09:42:39 | 

Unique visits. Through `session_id`, `user_id` and `user_type` you can track guests (logged out) and users (logged in).

#### Hits

| id | visitor_id | visit_id | route_id | path_id | query_id | referer_id | status_code | method   | protocol_version | is_no_cache | wants_json | is_secure | is_json | is_ajax | is_pjax | created_at |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 123 | 123 | 123 | 123 | 123 | 123 | 200 | GET/POST | HTTP/1.1 | 0 | 0 | 1 | 0 | 0 | 0 | 2022-05-07 09:42:39 |

This is the most comprehensive table that records every single request made to the project, with access details as seen
in the sample above.

> **Notes:**
> - As a final note, this package is a data hord, and it doesn't actually do much of the math that could be done on such a valuable gathered data, so it's up to your imagination to utilize it however you see fits your goals. Implementation details is up to you.
> - We didn't explain the `data` table since it's used for temporary raw data storage until it's being crunched and processed by the package, so you should **NOT** care or mess with that table. It's used internally by the package and has no real end-user usage.
> - The `\Rovereto\Metrika\Models\Hit` model has relationships to all related data such as `visitor`, `visit`, `agent`, `device`, `platform`, `path`, `route`, `referer`, `domain` and `geoip`. So once you grab a hit instance you can access any of it's relationships as you normaly do with [Eloquent Relationships](https://laravel.com/docs/master/eloquent-relationships) like so: `$hit->visitor->agent->version`, `$hit->visitor->platform->family` or `$hit->visit->geoip->city`.

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code
of conduct, and the process for submitting pull requests to us.

## Versioning

We use [Semantic Versioning](http://semver.org/) for versioning. For the versions
available, see the [tags on this repository](https://github.com/Ilyutkin/Metrika/tags).

## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.

## Support

The following support channels are available at your fingertips:

- [Help on Email](mailto:alexander@ilyutkin.ru)

## Author

- **Alexander Ilyutkin** [Ilyutkin](https://github.com/Ilyutkin)

See also the list of
[contributors](https://github.com/ilyutkin/metrika/contributors)
who participated in this project.

## License

This project is licensed under the [The MIT License (MIT)](LICENSE.md)
Massachusetts Institute of Technology License - see the [LICENSE.md](LICENSE.md) file for
details
