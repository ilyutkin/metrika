<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Datum extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'cookie_id',
        'session_id',
        'user_id',
        'user_type',
        'status_code',
        'uri',
        'method',
        'server',
        'input',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'cookie_id' => 'string',
        'session_id' => 'string',
        'user_id' => 'integer',
        'user_type' => 'string',
        'status_code' => 'integer',
        'uri' => 'string',
        'method' => 'string',
        'server' => 'json',
        'input' => 'json',
        'created_at' => 'datetime',
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

        $this->setTable(config('metrika.tables.data'));

        parent::__construct($attributes);
    }

    /**
     * Get the owning user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id', 'id');
    }

    /**
     * Get bookings of the given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUser(Builder $builder, Model $user): Builder
    {
        return $builder->where('user_type', $user->getMorphClass())->where('user_id', $user->getKey());
    }
}
