<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'family',
        'model',
        'brand',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'family' => 'string',
        'model' => 'string',
        'brand' => 'string',
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

        $this->setTable(config('metrika.tables.devices'));

        parent::__construct($attributes);
    }

    /**
     * The device may have many visitors.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitors(): HasMany
    {
        return $this->hasMany(config('metrika.models.visitor'), 'device_id', 'id');
    }
}
