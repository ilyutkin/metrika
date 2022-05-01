<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'name' => 'string'
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

        $this->setTable(config('metrika.tables.domains'));

        parent::__construct($attributes);
    }

    /**
     * The domain may have many referers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referers(): HasMany
    {
        return $this->hasMany(config('metrika.models.referer'), 'domain_id', 'id');
    }
}
