<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'host',
        'locale',
        'path',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'host' => 'string',
        'locale' => 'string',
        'path' => 'string',
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

        $this->setTable(config('metrika.tables.paths'));

        parent::__construct($attributes);
    }

    /**
     * The path may have many hits.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hits(): HasMany
    {
        return $this->hasMany(config('metrika.models.hit'), 'path_id', 'id');
    }
}
