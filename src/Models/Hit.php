<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Hit extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'visitor_id',
        'visit_id',
        'route_id',
        'path_id',
        'query_id',
        'referer_id',
        'status_code',
        'method',
        'protocol_version',
        'is_no_cache',
        'wants_json',
        'is_secure',
        'is_json',
        'is_ajax',
        'is_pjax',
        'created_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'visitor_id' => 'integer',
        'visit_id' => 'integer',
        'route_id' => 'integer',
        'path_id' => 'integer',
        'query_id' => 'integer',
        'referer_id' => 'integer',
        'status_code' => 'integer',
        'method' => 'string',
        'protocol_version' => 'string',
        'is_no_cache' => 'boolean',
        'wants_json' => 'boolean',
        'is_secure' => 'boolean',
        'is_json' => 'boolean',
        'is_ajax' => 'boolean',
        'is_pjax' => 'boolean',
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

        $this->setTable(config('metrika.tables.hits'));

        parent::__construct($attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $hit) {
            $hit->path()->increment('count');
            $hit->route()->increment('count');
            if($hit->referer) {
                $hit->referer()->increment('count');
                $hit->referer->domain()->increment('count');
            }
            $hit->visitor()->increment('count');
            $hit->visitor->agent()->increment('count');
            $hit->visitor->device()->increment('count');
            $hit->visitor->platform()->increment('count');
            $hit->visit()->increment('count');
            $hit->visit()->update(['last_view_at' => $hit->created_at]);
            $hit->visit->geoip()->increment('count');
        });
    }

    /**
     * The hit always belongs to a visitor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.visitor'), 'visitor_id', 'id', 'visitor');
    }

    /**
     * The hit always belongs to a visit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.visit'), 'visit_id', 'id', 'visit');
    }

    /**
     * The hit always belongs to a route.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.route'), 'route_id', 'id', 'route');
    }

    /**
     * The hit always belongs to a path.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.path'), 'path_id', 'id', 'path');
    }

    /**
     * The hit always belongs to a query.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function query_rel(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.query'), 'query_id', 'id', 'query');
    }

    /**
     * The hit always belongs to a referer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referer(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.referer'), 'referer_id', 'id', 'referer');
    }
}
