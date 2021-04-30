<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * This Eloquent model represents a reset token in the system. When a user
 * wants to reset his/her password, he/she should contact admin for a reset
 * token. He/she can then use the password reset page to reset his/her
 * password.
 *
 * In the DatabaseTokenRepository class, the email column is used to control
 * the email address where the reset token is sent to, as well as connecting
 * the reset token to a particular user. Since the system does not use email
 * at all, the email column is repurposed to store the user name despite its
 * name.
 *
 * @property string $id ID of the reset token.
 * @property string $email Name of the user the reset token belongs to.
 * @property string $token Text of the reset token.
 * @property Carbon $created_at Time when the reset token is created.
 */
class Reset extends Model
{
    /**
     * Name of the reset token table.
     * @var string
     */
    protected $table = 'urlmarker_resets';

    /**
     * Primary key of the reset token table.
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributes that are mass assignable.
     * @var string[]
     */
    protected $fillable = [
        'email', 'token',
    ];

    /**
     * Attributes that should be hidden for arrays.
     * @var string[]
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Attributes that are added to serialization.
     * @var string[]
     */
    protected $appends = [
        'expired', 'expired_at', 'relative_created_at', 'relative_updated_at', 'relative_expired_at',
    ];

    /**
     * Attributes that should be converted to other data types.
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Serialization format for dates. Since we use PostgreSQL timestamp
     * with timezone column type, we need to include the timezone information
     * also.
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Return whether the reset token has expired or not.
     * @return boolean
     */
    public function getExpiredAttribute(): bool
    {
        if ($this->getExpiredAtAttribute()->isFuture() === true) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return the token expiration time calculated from created_at column as
     * well as the relevant configuration.
     * @return Carbon\Carbon
     */
    public function getExpiredAtAttribute(): Carbon
    {
        $ttl = intval(config('auth.passwords.users.expire', 60 * 24 * 7), 10);
        $result = $this->created_at->copy();
        $result->addMinutes($ttl);
        return $result;
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
        return $this->getExpiredAtAttribute()->diffForHumans();
    }

    /**
     * Generate reset token string.
     * @return string
     */
    public static function generateToken(): string
    {
        return sprintf(
            '%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d%1d',
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9),
            random_int(0, 9), random_int(0, 9)
        );
    }
}
