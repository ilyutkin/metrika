<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Geoip extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'client_ip',
        'latitude',
        'longitude',
        'client_ips',
        'is_proxy',
        'proxy_type',
        'isp',
        'usage_type',
        'continent',
        'country_code',
        'country',
        'subdivision_code',
        'subdivision',
        'city',
        'timezone',
        'postal_code',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'client_ip' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'client_ips' => 'json',
        'is_proxy' => 'boolean',
        'proxy_type' => 'string',
        'isp' => 'string',
        'usage_type' => 'string',
        'continent' => 'string',
        'country_code' => 'string',
        'country' => 'string',
        'subdivision_code' => 'string',
        'subdivision' => 'string',
        'city' => 'string',
        'timezone' => 'string',
        'postal_code' => 'string',
    ];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('metrika.connection'));

        $this->setTable(config('metrika.tables.geoips'));

        parent::__construct($attributes);
    }

    public function getDivisionCodeAttribute()
    {
        return $this->subdivision_code;
    }

    /**
     * The geoip may have many visits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits(): HasMany
    {
        return $this->hasMany(config('metrika.models.visit'), 'geoip_id', 'id');
    }
}
