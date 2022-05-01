<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Visit extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'visitor_id',
        'user_id',
        'user_type',
        'session_id',
        'geoip_id',
        'referer_id',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'visitor_id' => 'integer',
        'user_id' => 'integer',
        'user_type' => 'string',
        'session_id' => 'string',
        'geoip_id' => 'integer',
        'referer_id' => 'integer',
        'created_at' => 'datetime',
        'last_view_at' => 'datetime',
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

        $this->setTable(config('metrika.tables.visits'));

        parent::__construct($attributes);
    }

    /**
     * The visit may have many hits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hits(): HasMany
    {
        return $this->hasMany(config('metrika.models.hit'), 'visit_id', 'id');
    }

    /**
     * The visit always belongs to a visitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.visitor'), 'visitor_id', 'id', 'visitor');
    }

    /**
     * The visit always belongs to an geoip.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function geoip(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.geoip'), 'geoip_id', 'id', 'geoip');
    }

    /**
     * The visit always belongs to a referer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referer(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.referer'), 'referer_id', 'id', 'referer');
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
//    public function scopeOfUser(Builder $builder, Model $user): Builder
//    {
//        return $builder->where('user_type', $user->getMorphClass())->where('user_id', $user->getKey());
//    }
}
