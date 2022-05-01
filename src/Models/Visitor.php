<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Visitor extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'cookie_id',
        'agent_id',
        'device_id',
        'platform_id',
        'language',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'user_id' => 'integer',
        'user_type' => 'string',
        'cookie_id' => 'string',
        'agent_id' => 'integer',
        'device_id' => 'integer',
        'platform_id' => 'integer',
        'language' => 'string',
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

        $this->setTable(config('metrika.tables.visitors'));

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

    /**
     * The visitor always belongs to an agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.agent'), 'agent_id', 'id', 'agent');
    }

    /**
     * The visitor always belongs to a device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.device'), 'device_id', 'id', 'device');
    }

    /**
     * The visitor always belongs to a platform.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.platform'), 'platform_id', 'id', 'platform');
    }
}
