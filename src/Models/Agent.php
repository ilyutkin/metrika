<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'kind',
        'family',
        'version',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'name' => 'string',
        'kind' => 'string',
        'family' => 'string',
        'version' => 'string',
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

        $this->setTable(config('metrika.tables.agents'));

        parent::__construct($attributes);
    }

    /**
     * The agent may have many visitors.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitors(): HasMany
    {
        return $this->hasMany(config('metrika.models.visitor'), 'agent_id', 'id');
    }
}
