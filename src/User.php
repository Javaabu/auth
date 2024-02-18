<?php

namespace Javaabu\Auth;

use App\Mail\EmailUpdated;
use App\Mail\NewEmailVerification;
use App\Notifications\EmailUpdateRequest;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatable;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatableContract;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Helpers\Media\UpdateMedia;
use Javaabu\Helpers\Traits\HasStatus;
use Javaabu\Passport\Traits\HasUserIdentifier;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class User extends Authenticatable implements
    HasMedia,
    AdminModel,
    MustVerifyEmail,
    PasswordUpdatableContract,
    UserContract
{
    use CausesActivity;
    use HasApiTokens;
    use HasStatus;
    use HasUserIdentifier;
    use InteractsWithMedia;
    use IsAdminModel;
    use LogsActivity;
    use Notifiable;
    use PasswordUpdatable;
    use SoftDeletes;
    use UpdateMedia;

    protected static $status_class = UserStatuses::class;

    /**
     * The attributes that would be logged
     *
     * @var array
     */
    protected static $logAttributes = ['*'];

    /**
     * Log only changed attributes
     *
     * @var boolean
     */
    protected static $logOnlyDirty = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email'
    ];

    /**
     * The attributes that are dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'email_verified_at'
    ];

    /**
     * Get the user identifier
     */
    public function getPassportCookieIdentifier()
    {
        return $this->makeUserIdentifier($this->getKey(), $this->getMorphClass());
    }

    /**
     * Convert dates to Carbon
     *
     * @param $date
     */
    public function setLastLoginAtAttribute($date)
    {
        $this->attributes['last_login_at'] = $date ? Carbon::parse($date) : null;
    }

    /**
     * Hash the password before saving
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Send email verification link to
     * new email address
     */
    public function sendNewEmailVerification()
    {
        Mail::to($this->new_email)
            ->send(new NewEmailVerification($this));
    }

    /**
     * Notify the current email that
     * there has been a request to change
     * the email address
     *
     * @param $new_email
     */
    public function sendEmailUpdateRequestNotification($new_email)
    {
        $this->notify(new EmailUpdateRequest($new_email));
    }

    /**
     * Inform the old email that it has
     * been updated to the new email
     *
     * @param $old_email
     * @param $new_email
     */
    public function sendEmailUpdatedNotification($old_email, $new_email)
    {
        Mail::to($old_email)
            ->send(new EmailUpdated($this, $new_email));
    }

    /**
     * Update the email
     */
    public function updateEmail()
    {
        if ($this->new_email) {
            // note the current email
            $old_email = $this->email;

            // set the email to the new email
            $this->email = $this->new_email;
            $this->new_email = null;
            $this->save();

            //notify old email
            if ($old_email != $this->email) {
                $this->sendEmailUpdatedNotification($old_email, $this->email);
            }

            return $this->email;
        }

        return false;
    }

    /**
     * Request for an email update
     * Saves the new email until it can be verified
     *
     * @param $new_email
     * @return bool
     */
    public function requestEmailUpdate($new_email)
    {
        //check if new email is different
        if ($new_email && $this->email != $new_email) {
            $this->new_email = $new_email;

            // send the email_token
            $this->sendNewEmailVerification();

            // inform current email
            $this->sendEmailUpdateRequestNotification($new_email);

            return $new_email;
        }

        return false;
    }

    /**
     * Approve the user
     *
     * @param  bool  $save
     */
    public function approve($save = false)
    {
        $this->updateStatus(UserStatuses::APPROVED, $save);
    }

    /**
     * Set the user to pending
     *
     * @param  bool  $save
     */
    public function markAsPending($save = false)
    {
        $this->updateStatus(UserStatuses::PENDING, $save);
    }

    /**
     * Ban the user
     *
     * @param  bool  $save
     */
    public function ban($save = false)
    {
        $this->updateStatus(UserStatuses::BANNED, $save);
    }

    /**
     * Only allow active users for passport
     *
     * @param $username
     * @return mixed
     */
    public function findForPassport($username)
    {
        return $this->where('email', $username)
            ->active()
            ->first();
    }

    /**
     * Get pending key
     *
     * @return int
     */
    public function getPendingKey()
    {
        return UserStatuses::PENDING;
    }

    /**
     * Pending users scope
     *
     * @param $query
     * @return
     */
    public function scopePending($query)
    {
        return $query->where($this->getTable() . '.status', $this->getPendingKey());
    }

    /**
     * Check if new email is available
     *
     * @return boolean
     */
    public function isNewEmailAvailable()
    {
        return !static::where('email', $this->new_email)
            ->withTrashed()
            ->exists();
    }

    /**
     * Check whether the current user's email should be verified
     *
     * @return bool
     */
    public function shouldVerifyEmail()
    {
        return $this->status != UserStatuses::BANNED;
    }

    /**
     * Check if wants new email
     *
     * @return boolean
     */
    public function wantsNewEmail()
    {
        return $this->hasVerifiedEmail() && $this->new_email;
    }

    /**
     * Check if needs email verification
     * Email verification is needed if the user is
     * allowed to verify email and does not have
     * a verified email or wants a new email
     *
     * @return boolean
     */
    public function needsEmailVerification()
    {
        return $this->shouldVerifyEmail() &&
            (!$this->hasVerifiedEmail() || $this->wantsNewEmail());
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->wantsNewEmail() ? $this->new_email : $this->email;
    }

    /**
     * Email unverified scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeEmailUnverified($query)
    {
        return $query->whereNull($this->getTable() . '.email_verified_at');
    }

    /**
     * Email verified scope
     *
     * @param $query
     * @return
     */
    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull($this->getTable() . '.email_verified_at');
    }

    /**
     * Get the user status message
     *
     * @return string
     */
    public function getStatusMessageAttribute()
    {
        if ($this->is_locked_out) {
            return __('Your account has been locked due to too many login attempts. ' .
                'Contact our staff to reset your account password');
        }

        if ($this->shouldVerifyEmail() && !$this->hasVerifiedEmail()) {
            return __('Please verify your email address to access your account.');
        }

        return UserStatuses::getMessage($this->status);
    }

    /**
     * Set email verified at
     *
     * @param  bool  $verified
     * @return void
     */
    public function setEmailVerificationStatus($verified)
    {
        if ($verified) {
            if (!$this->hasVerifiedEmail()) {
                $this->email_verified_at = $this->freshTimestamp();
            }
        } else {
            $this->email_verified_at = null;
        }
    }

    /**
     * Should verify email scope
     *
     * @param $query
     * @return
     */
    public function scopeShouldVerifyEmail($query)
    {
        return $query->where('status', '!=', UserStatuses::BANNED);
    }

    /**
     * Approved users scope
     *
     * @param $query
     * @return
     */
    public function scopeApproved($query)
    {
        return $query->where($this->getTable() . '.status', UserStatuses::APPROVED);
    }

    /**
     * Active users scope
     *
     * @param $query
     * @return
     */
    public function scopeActive($query)
    {
        return $query->approved()
            ->emailVerified()
            ->notLockedOut();
    }

    /**
     * Check if the user is active
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        return $this->hasVerifiedEmail() &&
            $this->status == UserStatuses::APPROVED &&
            (!$this->is_locked_out);
    }

    /**
     * Get the max login attempts
     *
     * @return int
     */
    public function maxLoginAttempts()
    {
        return config('auth.max_login_attempts');
    }

    /**
     * Check if is locked out
     *
     * @return boolean
     */
    public function getIsLockedOutAttribute()
    {
        return $this->login_attempts >= $this->maxLoginAttempts();
    }

    /**
     * Locked out users scope
     *
     * @param $query
     * @return
     */
    public function scopeLockedOut($query)
    {
        return $query->where('login_attempts', '>=', $this->maxLoginAttempts());
    }

    /**
     * Not locked out users scope
     *
     * @param $query
     * @return
     */
    public function scopeNotLockedOut($query)
    {
        return $query->whereNull('login_attempts')
            ->orWhere('login_attempts', '<', $this->maxLoginAttempts());
    }

    /**
     * Check if is approved
     *
     * @return bool
     */
    public function getIsApprovedAttribute()
    {
        return $this->status == UserStatuses::APPROVED;
    }

    /**
     * Check if is banned
     *
     * @return bool
     */
    public function getIsBannedAttribute()
    {
        return $this->status == UserStatuses::BANNED;
    }

    /**
     * Check if is pending
     *
     * @return bool
     */
    public function getIsPendingAttribute()
    {
        return $this->status == UserStatuses::PENDING;
    }

    /**
     * Check if the user has any of the following permissions
     *
     * @param  array|string  $permissions
     * @return bool
     */
    public function anyPermission($permissions)
    {
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * User visible
     *
     * @param $query
     * @return mixed
     */
    public function scopeUserVisible($query)
    {
        $user = auth()->user();

        if ($user) {
            if ($user->can('create', static::class)) {
                //can view all
                return $query;
            }
        }

        // everyone can view published
        return $query->approved();
    }

    /**
     * Get is published attribute
     *
     * @return bool
     */
    public function getIsPublishedAttribute()
    {
        return $this->is_approved;
    }

    /**
     * Get email verification redirect url
     *
     * @return string
     */
    public function emailVerificationRedirectUrl()
    {
        return $this->homeUrl();
    }

    /**
     * Get initials attribute
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', preg_replace('/\s+/', ' ', $this->name));
        $initials = substr($names[0] ?? '', 0, 1);

        if (($count = count($names)) > 1) {
            $initials .= substr($names[$count - 1], 0, 1);
        }

        return $initials;
    }

    /**
     * Increment login attempts
     *
     * @return void
     */
    public function incrementLoginAttempts(): void
    {
        $this->login_attempts = ($this->login_attempts ?: 0) + 1;
    }

    /**
     * Reset login attempts
     *
     * @return void
     */
    public function resetLoginAttempts()
    {
        $this->login_attempts = null;
    }

    /**
     * Define media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsFile(function (File $file) {
                return AllowedMimeTypes::isAllowedMimeType($file->mimeType, 'image');
            });
    }

    /**
     * Register image conversions
     *
     * @param  Media|null  $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('avatar')
            ->width(200)
            ->height(200)
            ->fit('crop', 200, 200)
            ->keepOriginalImageFormat()
            ->performOnCollections('avatar');

        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400);
    }

    /**
     * Get the avatar url
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        $avatar = $this->getFirstMediaUrl('avatar', 'avatar');
        return $avatar ?: asset(get_setting('default_avatar'));
    }

    /**
     * Get the provider for this user type
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->getTable();
    }

    /**
     * Get the list name
     *
     * @return string
     */
    public function getListNameAttribute(): string
    {
        return __(':name (:email)', ['name' => $this->name, 'email' => $this->email]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(static::$logAttributes);
    }
}
