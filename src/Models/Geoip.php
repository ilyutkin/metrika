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
        'country_code',
        'client_ips',
        'is_from_trusted_proxy',
        'division_code',
        'postal_code',
        'timezone',
        'city',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'client_ip' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'country_code' => 'string',
        'client_ips' => 'json',
        'is_from_trusted_proxy' => 'boolean',
        'division_code' => 'string',
        'postal_code' => 'string',
        'timezone' => 'string',
        'city' => 'string',
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
