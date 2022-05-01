<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referer extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'domain_id',
        'url',
        'medium',
        'source'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'domain_id' => 'integer',
        'url' => 'string',
        'medium' => 'string',
        'source' => 'string'
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

        $this->setTable(config('metrika.tables.referers'));

        parent::__construct($attributes);
    }

    /**
     * The referer always belongs to a domain.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(config('metrika.models.domain'), 'domain_id', 'id', 'domain');
    }

    /**
     * The referer may have many hits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hits(): HasMany
    {
        return $this->hasMany(config('metrika.models.hit'), 'referer_id', 'id');
    }

    /**
     * The referer may have many visits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits(): HasMany
    {
        return $this->hasMany(config('metrika.models.visit'), 'referer_id', 'id');
    }
}
