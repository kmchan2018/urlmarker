<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This Eloquent model represents a marker in the system.
 *
 * @property string $id ID of the marker.
 * @property string $url URL of the marker.
 * @property ?string $description Description of the marker.
 * @property ?string $handler Handler that adds the marker to the database.
 * @property Carbon $created_at Time when the marker is created.
 * @property Carbon $updated_at Time when the marker is udpated.
 * @property ?Carbon $deleted_at Time when the marker is deleted.
 */
class Marker extends Model
{
    use SoftDeletes;

    /**
     * Name of the marker table.
     * @var string
     */
    protected $table = 'urlmarker_markers';

    /**
     * Primary key of the marker table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = [
        'url', 'description', 'handler',
    ];

    /**
     * Attributes that are hidden from serialization.
     * @var string[]
     */
    protected $hidden = [
        'user',
    ];

    /**
     * Attributes that are added to serialization.
     * @var string[]
     */
    protected $appends = [
        'relative_created_at', 'relative_updated_at', 'relative_deleted_at',
    ];

    /**
     * Attributes that should be converted to other data types.
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Serialization format for dates. Since we use PostgreSQL timestamp with
     * timezone column type, we need to include the timezone information also.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Returns the user this marker belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\\Models\\User', 'user');
    }

    /**
     * Return the computed attribute relative_created_at which contains the
     * created_at value relative to the time when the method is called.
     * @return string
     */
    public function getRelativeCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Return the computed attribute relative_updated_at which contains the
     * updated_at value relative to the time when the method is called.
     * @return string
     */
    public function getRelativeUpdatedAtAttribute(): string
    {
        return $this->updated_at->diffForHumans();
    }

    /**
     * Return the computed attribute relative_deleted_at which contains the
     * deleted_at value relative to the time when the method is called.
     * @return ?string
     */
    public function getRelativeDeletedAtAttribute(): ?string
    {
        if ($this->deleted_at !== null) {
            return $this->deleted_at->diffForHumans();
        } else {
            return null;
        }
    }
}
