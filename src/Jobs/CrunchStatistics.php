<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent as UserAgent;
use Snowplow\RefererParser\Parser as RefererParser;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use UAParser\Parser;

class CrunchStatistics implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('metrika.datum')->each(function ($item) {
            try {
                $symfonyRequest = SymfonyRequest::create($item['uri'], $item['server']['REQUEST_METHOD'], $item['input'] ?? [], [], [], $item['server']);
                $symfonyRequest->overrideGlobals();

                LaravelRequest::enableHttpMethodParameterOverride();
                $laravelRequest = LaravelRequest::createFromBase($symfonyRequest);
                $laravelRoute = app('router')->getRoutes()->match($laravelRequest);
                $laravelRequest->setRouteResolver(function () use ($laravelRoute) {
                    return $laravelRoute;
                });

                $tokens = [];
                collect($laravelRequest->route()->getCompiled()->getTokens())->map(function ($item) use (&$tokens) {
                    return ($item = collect($item)) && $item->contains('variable') ? $tokens[$item[3]] = $item[2] : null;
                });
                $route = app('metrika.route')->firstOrCreate([
                    'name' => $laravelRoute->getName() ?: $laravelRoute->uri(),
                ], [
                    'path' => $laravelRoute->uri(),
                    'action' => $laravelRoute->getActionName(),
                    'middleware' => $laravelRoute->gatherMiddleware() ?: null,
                    'parameters' => $tokens ?: null,
                ]);

                $path = app('metrika.path')->firstOrCreate([
                    'host' => $laravelRequest->getHost(),
                    'path' => $laravelRequest->decodedPath(),
                    'method' => $laravelRequest->getMethod(),
                    'locale' => $laravelRequest->route('locale') ?? app()->getLocale(),
                ], [
                    'parameters' => $item['input'] ?: null,
                ]);

                $referer_id = $this->findOrCreateReferer($laravelRequest);

                $visitor = $this->findOrCreateVisitor($item, $laravelRequest);

                $visit = $this->findOrCreateVisit($item, $visitor, $referer_id, $laravelRequest);

                app('metrika.hit')->create([
                    'visitor_id' => $visitor->getKey(),
                    'visit_id' => $visit->getKey(),
                    'route_id' => $route->getKey(),
                    'path_id' => $path->getKey(),
                    'referer_id' => $referer_id,
                    'status_code' => $item['status_code'],
                    'method' => $laravelRequest->getMethod(),
                    'protocol_version' => $laravelRequest->getProtocolVersion(),
                    'is_no_cache' => $laravelRequest->isNoCache(),
                    'wants_json' => $laravelRequest->wantsJson(),
                    'is_secure' => $laravelRequest->isSecure(),
                    'is_json' => $laravelRequest->isJson(),
                    'is_ajax' => $laravelRequest->ajax(),
                    'is_pjax' => $laravelRequest->pjax(),
                    'created_at' => $item['created_at'],
                ]);

                $item->delete();
            } catch (Exception $exception) {
                Log::error($exception->getMessage() . ";\nline:" . $exception->getFile() . ':' . $exception->getLine());
                Log::error(print_r($exception->getTrace(), true));

                dump($exception->getMessage());
            }
        });
    }

    protected function findOrCreateReferer($laravelRequest)
    {
        $refererUrl = $laravelRequest->header('referer') ?: $laravelRequest->input('utm_source');

        if ($refererUrl) {
            $url = parse_url($refererUrl);

            if (!isset($url['host'])) {
                return null;
            }

            $domain = app('metrika.domain')->firstOrCreate([
                'name' => $url['host']
            ]);

            $referer_parser = new RefererParser(null, [$laravelRequest->getHost()]);
            $parsed = $referer_parser->parse($refererUrl, $laravelRequest->getUri());

            $referer = app('metrika.referer')->firstOrCreate([
                'domain_id' => $domain->getKey(),
                'url' => $refererUrl
            ], [
                'medium' => $parsed->getMedium(),
                'source' => $parsed->getSource()
            ]);
            return $referer->id;
        }
        return null;
    }

    protected function findOrCreateVisitor($item, $laravelRequest)
    {
        $visitor = app('metrika.visitor')->where('cookie_id', $item['cookie_id'])->first();

        if (!$visitor) {
            $user_agent = new UserAgent($item['server']);
            $UAParser = Parser::create()->parse($user_agent->getUserAgent());
            $kind = $user_agent->isDesktop() ? 'desktop' : ($user_agent->isTablet() ? 'tablet' : ($user_agent->isPhone() ? 'phone' : ($user_agent->isRobot() ? 'robot' : 'unknown')));

            $agent = app('metrika.agent')->firstOrCreate([
                'name' => $user_agent->getUserAgent(),
                'kind' => $kind,
                'family' => $UAParser->ua->family,
                'version' => $UAParser->ua->toVersion(),
            ]);

            $device = app('metrika.device')->firstOrCreate([
                'family' => $UAParser->device->family,
                'model' => $UAParser->device->model,
                'brand' => $UAParser->device->brand,
            ]);

            $platform = app('metrika.platform')->firstOrCreate([
                'family' => $UAParser->os->family,
                'version' => $UAParser->os->toVersion(),
            ]);

            $visitor = app('metrika.visitor')->create([
                'cookie_id' => $item['cookie_id'],
                'user_id' => $item['user_id'],
                'user_type' => $item['user_type'],
                'agent_id' => $agent->getKey(),
                'device_id' => $device->getKey(),
                'platform_id' => $platform->getKey(),
                'language' => $laravelRequest->getPreferredLanguage(),
                'created_at' => $item['created_at'],
            ]);
        }
        return $visitor;
    }

    protected function findOrCreateVisit($item, $visitor, $referer_id, $laravelRequest)
    {
        $visit = null;
        //Find last visit by session
        $visit_last = app('metrika.visit')->where('visitor_id', $visitor->getKey())
            ->where('session_id', $item['session_id'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($visit_last) {
            if (!config('metrika.visit_close_time')) {
                return $visit_last;
            } else {
                $hit = $visit_last->hits()->orderBy('created_at', 'desc')->first();

                if ($hit) {
                    $last_date = $hit->created_at;
                    $last_date->addMinutes(config('metrika.visit_close_time'));

                    if ($last_date->gt($item['created_at'])) {
                        return $visit_last;
                    }
                }
                $visit_last = null;
            }
        }

        $ip = $laravelRequest->getClientIp();

        $country_code = geoip($ip)->getAttribute('iso_code');
        if ($country_code)
            $country_code = mb_strtoupper($country_code);

        $geoip = app('metrika.geoip')->firstOrCreate([
            'client_ip' => $ip = $laravelRequest->getClientIp(),
            'latitude' => geoip($ip)->getAttribute('lat'),
            'longitude' => geoip($ip)->getAttribute('lon'),
        ], [
            'client_ips' => $laravelRequest->getClientIps() ?: null,
            'country_code' => $country_code,
            'is_from_trusted_proxy' => $laravelRequest->isFromTrustedProxy(),
            'division_code' => geoip($ip)->getAttribute('state'),
            'postal_code' => geoip($ip)->getAttribute('postal_code'),
            'timezone' => geoip($ip)->getAttribute('timezone'),
            'city' => geoip($ip)->getAttribute('city'),
        ]);

        return app('metrika.visit')->create([
            'visitor_id' => $visitor->getKey(),
            'session_id' => $item['session_id'],
            'geoip_id' => $geoip->getKey(),
            'referer_id' => $referer_id,
            'user_id' => $item['user_id'],
            'user_type' => $item['user_type'],
            'created_at' => $item['created_at'],
        ]);
    }
}
