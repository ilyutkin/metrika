<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'action',
        'middleware',
        'path',
        'parameters',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'name' => 'string',
        'action' => 'string',
        'middleware' => 'json',
        'path' => 'string',
        'parameters' => 'json',
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

        $this->setTable(config('metrika.tables.routes'));

        parent::__construct($attributes);
    }

    /**
     * The route may have many hits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hits(): HasMany
    {
        return $this->hasMany(config('metrika.models.hit'), 'route_id', 'id');
    }
}
