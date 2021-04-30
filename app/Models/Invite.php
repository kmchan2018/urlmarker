<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * This Eloquent model represents an invite code in the system. To create a
 * new user in the system, the user have to first ask fort an invite code.
 * He/she can then create a new user account using the invite code.
 *
 * @property string $id ID of the invite code.
 * @property string $code Content of the invite code.
 * @property ?string $notes Notes for the invite code.
 * @property bool $expired Whether the invite code has expired or not.
 * @property Carbon $created_at Time when the invite code is created.
 * @property Carbon $updated_at Time when the invite code is udpated.
 * @property Carbon $expired_at Time when the invite code is expired.
 */
class Invite extends Model
{
    /**
     * Name of the invite code table.
     * @var string
     */
    protected $table = 'urlmarker_invites';

    /**
     * Primary key of the invite code table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = [
        'code', 'notes', 'expired_at',
    ];

    /**
     * Attributes that are added to serialization.
     * @var string[]
     */
    protected $appends = [
        'expired', 'relative_created_at', 'relative_updated_at', 'relative_expired_at',
    ];

    /**
     * Attributes that should be converted to other data types.
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Serialization format for dates. Since we use PostgreSQL timestamp with
     * timezone column type, we need to include the timezone information also.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Return if the invite code has expired. It is only true when the invite
     * code is active and the current timestamp is larger than the expired_at
     * timestamp of the invite code.
     * @return bool
     */
    public function getExpiredAttribute(): bool
    {
        if ($this->expired_at->isFuture() === true) {
            return false;
        } else {
            return true;
        }
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
     * Return the computed attribute relative_expired_at which contains the
     * expired_at value relative to the time when the method is called.
     * @return string
     */
    public function getRelativeExpiredAtAttribute(): string
    {
        return $this->expired_at->diffForHumans();
    }
}
