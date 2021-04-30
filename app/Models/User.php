<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * This Eloquent model represents a user in the system. The design of the user
 * account system should be pretty standard except two points:
 *
 * First of all, users are identified by name instead of email address. In
 * fact, the system does not even store any email addresses at all. It causes
 * some complications in password reset where email address is expected.
 *
 * Next, there is only a single type of user account in the system. It is
 * represented by this class. The "role" attribute decides what the account
 * can do. For example, only users with admin role can create invite codes
 * and reset tokens.
 *
 * @property string $id ID of the user.
 * @property string $name Name of the user.
 * @property string $password Password of the user.
 * @property int $role Role of the user.
 * @property int $status Status of the user.
 * @property Carbon $created_at Time when the user is created.
 * @property Carbon $updated_at Time when the user is udpated.
 */
class User extends Authenticatable implements CanResetPassword
{
    use Notifiable;

    /**
     * Role code for normal user.
     */
    const NORMAL = 0;

    /**
     * Role code for administrator.
     */
    const ADMIN = 1;

    /**
     * Status code for active user.
     */
    const ACTIVE = 1;

    /**
     * Status code for new created user.
     */
    const CREATED = 0;

    /**
     * Status code for terminated user.
     */
    const TERMINATED = -1;

    /**
     * Status code for suspended user.
     */
    const SUSPENDED = -2;

    /**
     * Name of the user table.
     * @var string
     */
    protected $table = 'urlmarker_users';

    /**
     * Primary key of the user table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = [
        'name', 'password', 'role', 'status',
    ];

    /**
     * Attributes that should be hidden for arrays.
     * @var string[]
     */
    protected $hidden = [
        'id', 'password', 'remember_token',
    ];

    /**
     * Attributes that are added to serialization.
     * @var string[]
     */
    protected $appends = [
        'relative_created_at', 'relative_updated_at',
    ];

    /**
     * Attributes that should be converted to other data types.
     * @var array<string,string>
     */
    protected $casts = [
        'role' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Serialization format for dates. Since we use PostgreSQL timestamp with
     * timezone column type, we need to include the timezone information also.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Returns a list of markers that belongs to this user.
     */
    public function markers(): HasMany
    {
        return $this->hasMany('App\\Models\\Marker', 'user');
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
     * Return the unique email address which identifies the user and controls
     * where reset tokens are sent. Since a user do not have email address and
     * the system does not send reset tokens over email, the function returns
     * the user name as is.
     * @return string
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->name;
    }

    /**
     * Send the password reset notification. Since the system relies on manual
     * notification of reset tokens, the function does nothing here.
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        // do nothing
    }
}
