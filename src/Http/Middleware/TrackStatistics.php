<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rovereto\Metrika\Models\Datum;
use Rovereto\Metrika\Jobs\CrunchStatistics;
use Rovereto\Metrika\Jobs\CleanStatisticsRequests;

class TrackStatistics
{
    protected static $cookie_id = null;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (config('metrika.store_cookie')) {
            if(!self::$cookie_id = $request->cookie(config('metrika.cookie_name'))) {
                self::$cookie_id = Str::uuid();
            }
            $response->withCookie(cookie(config('metrika.cookie_name'), self::$cookie_id, config('metrika.cookie_lifetime')));
        }

        return $response;
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param \Illuminate\Http\Request                   $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     *
     * @return void
     */
    public function terminate($request, $response): void
    {
        $currentUser = $request->user();

        Datum::create([
            'cookie_id' => self::getCookie($request),
            'session_id' => $request->session()->getId(),
            'user_id' => $currentUser?->getKey(),
            'user_type' => $currentUser?->getMorphClass(),
            'status_code' => $response->getStatusCode(),
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'server' => $request->server() ?: null,
            'input' => $request->input() ? $request->except(config('metrika.exclude_input_fields')) : null,
            'created_at' => Carbon::now(),
        ]);

        // Here we will see if this request hits the statistics crunching lottery by hitting
        // the odds needed to perform statistics crunching on any given request. If we do
        // hit it, we'll call this handler to let it crunch numbers and the hard work.
        if ($this->configHitsLottery()) {
            CrunchStatistics::dispatch();

            // Now let's do some garbage collection and clean old statistics requests
            CleanStatisticsRequests::dispatch();
        }
    }

    public static function record(Request $request, $status_code = 404): void
    {
        $currentUser = $request->user();

        $cookie_id = null;
        if (config('metrika.store_cookie')) {
            $cookie_id = $request->cookie(config('metrika.cookie_name'));
        }
        if(empty($cookie_id)) {
            $cookie_id = $request->session()->getId();
        }

        Datum::create([
            'cookie_id' => $cookie_id,
            'session_id' => $request->session()->getId(),
            'user_id' => $currentUser?->getKey(),
            'user_type' => $currentUser?->getMorphClass(),
            'status_code' => $status_code,
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'server' => $request->server() ?: null,
            'input' => $request->input() ? $request->except(config('metrika.exclude_input_fields')) : null,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Return Cookie UUID
     * @param $request
     * @return mixed|null
     */
    public static function getCookie($request)
    {
        $cookie_id = $request->session()->getId();
        if (config('metrika.store_cookie')) {
            if(!$cookie_id = $request->cookie(config('metrika.cookie_name'))){
                $cookie_id = self::$cookie_id;
            }
        }
        return $cookie_id;
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @return bool
     */
    protected function configHitsLottery(): bool
    {
        $config = config('metrika.lottery');

        return $config ? random_int(1, $config[1]) <= $config[0] : false;
    }
}
