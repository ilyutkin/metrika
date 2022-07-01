<?php

namespace Rovereto\Metrika\Services;

use IP2Location\Database;
use Rovereto\Country\Concerns\Continent;
use Rovereto\Country\Concerns\Subdivision;
use Rovereto\Country\Concerns\TimeZone;
use Torann\GeoIP\Services\AbstractService;

class Ip2Location extends AbstractService
{
    use Continent, Subdivision, TimeZone;

    /**
     * Service database instance.
     *
     * @var \IP2Location\Database
     */
    protected $db;

    /**
     * The "booting" method of the service.
     *
     * @return void
     */
    public function boot()
    {
        $path = $this->config('database_path');

        $this->db = new Database($path, Database::FILE_IO);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($ip)
    {
        $record = $this->db->lookup($ip, Database::ALL);

        $subdivision_code = $this->getSubdivisionCode($record['countryCode'], $record['regionName']);

        $timezone = $this->getTimeZone($record['countryCode'], $subdivision_code);

        $continent_code = $this->getContinentCodeByCountry($record['countryCode'], $subdivision_code);

        return $this->hydrate([
            'ip' => $ip,
            'iso_code' => $record['countryCode'],
            'country' => $record['countryName'],
            'city' => $record['cityName'],
            'state' => $subdivision_code,
            'state_name' => $record['regionName'],
            'postal_code' => $record['zipCode'],
            'lat' => $record['latitude'],
            'lon' => $record['longitude'],
            'timezone' => $timezone ?? $record['timeZone'],
            'continent' => $continent_code,
        ]);
    }
}
