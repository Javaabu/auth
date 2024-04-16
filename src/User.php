<?php

namespace Javaabu\Auth;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Javaabu\Activitylog\Traits\LogsActivity;
use Javaabu\Auth\Enums\UserStatuses;
use Javaabu\Auth\Mail\EmailUpdated;
use Javaabu\Auth\Mail\NewEmailVerification;
use Javaabu\Auth\Notifications\EmailUpdateRequest;
use Javaabu\Auth\Notifications\ResetPassword;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatable;
use Javaabu\Auth\PasswordUpdate\PasswordUpdatableContract;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Helpers\Media\AllowedMimeTypes;
use Javaabu\Helpers\Media\UpdateMedia;
use Javaabu\Helpers\Traits\HasStatus;
use Javaabu\Passport\Traits\HasUserIdentifier;
use Laravel\Passport\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class User extends Authenticatable implements AdminModel, HasMedia, MustVerifyEmail, PasswordUpdatableContract, UserContract
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

    protected static string $status_class = UserStatuses::class;

    /**
     * The attributes that would be logged
     *
     * @var array
     */
    protected static array $logAttributes = ['*'];

    /**
     * Never log these attributes
     *
     * @var array
     */
    protected static array $logExceptAttributes = [
        'password',
        'remember_token',
    ];

    /**
     * Ignore changes to these attributes
     *
     * @var array|string[]
     */
    protected static array $ignoreChangedAttributes = [
        'updated_at',
        'created_at',
        'last_login_at',
        'remember_token',
        'login_attempts',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
    ];

    /**
     * The attributes that are cast to native types
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'status' => UserStatuses::class,
    ];

    /**
     * The searchable attributes
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'email',
    ];

    /**
     * Get the user identifier
     */
    public function getPassportCookieIdentifier(): string
    {
        return $this->makeUserIdentifier($this->getKey(), $this->getMorphClass());
    }

    /**
     * Convert dates to Carbon
     */
    public function setLastLoginAtAttribute($date): void
    {
        $this->attributes['last_login_at'] = $date ? Carbon::parse($date) : null;
    }

    /**
     * Hash the password before saving
     */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Send email verification link to
     * new email address
     */
    public function sendNewEmailVerification(): void
    {
        Mail::to($this->new_email)
            ->send(new NewEmailVerification($this));
    }

    /**
     * Notify the current email that
     * there has been a request to change
     * the email address
     */
    public function sendEmailUpdateRequestNotification($new_email): void
    {
        $this->notify(new EmailUpdateRequest($new_email));
    }

    /**
     * Inform the old email that it has
     * been updated to the new email
     */
    public function sendEmailUpdatedNotification($old_email, $new_email): void
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
     */
    public function requestEmailUpdate($new_email): bool
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
    public function approve($save = false): void
    {
        $this->updateStatus(UserStatuses::APPROVED, $save);
    }

    /**
     * Set the user to pending
     *
     * @param  bool  $save
     */
    public function markAsPending($save = false): void
    {
        $this->updateStatus(UserStatuses::PENDING, $save);
    }

    /**
     * Ban the user
     *
     * @param  bool  $save
     */
    public function ban($save = false): void
    {
        $this->updateStatus(UserStatuses::BANNED, $save);
    }

    /**
     * Only allow active users for passport
     */
    public function findForPassport($username): mixed
    {
        return $this->where('email', $username)
            ->active()
            ->first();
    }

    /**
     * Get pending key
     */
    public function getPendingKey(): UserStatuses
    {
        return UserStatuses::PENDING;
    }

    /**
     * Pending users scope
     */
    public function scopePending($query)
    {
        return $query->where($this->getTable().'.status', $this->getPendingKey());
    }

    /**
     * Check if new email is available
     */
    public function isNewEmailAvailable(): bool
    {
        return ! static::where('email', $this->new_email)
            ->withTrashed()
            ->exists();
    }

    /**
     * Check whether the current user's email should be verified
     */
    public function shouldVerifyEmail(): bool
    {
        return $this->status != UserStatuses::BANNED;
    }

    /**
     * Check if wants new email
     */
    public function wantsNewEmail(): bool
    {
        return $this->hasVerifiedEmail() && $this->new_email;
    }

    /**
     * Check if needs email verification
     * Email verification is needed if the user is
     * allowed to verify email and does not have
     * a verified email or wants a new email
     */
    public function needsEmailVerification(): bool
    {
        return $this->shouldVerifyEmail() &&
            (! $this->hasVerifiedEmail() || $this->wantsNewEmail());
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->wantsNewEmail() ? $this->new_email : $this->email;
    }

    /**
     * Email unverified scope
     */
    public function scopeEmailUnverified($query): mixed
    {
        return $query->whereNull($this->getTable().'.email_verified_at');
    }

    /**
     * Email verified scope
     */
    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull($this->getTable().'.email_verified_at');
    }

    /**
     * Get the user status message
     */
    public function getStatusMessageAttribute(): string
    {
        if ($this->is_locked_out) {
            return __('Your account has been locked due to too many login attempts. '.
                'Contact our staff to reset your account password');
        }

        if ($this->shouldVerifyEmail() && ! $this->hasVerifiedEmail()) {
            return __('Please verify your email address to access your account.');
        }

        return $this->status->getMessage();
    }

    /**
     * Set email verified at
     *
     * @param  bool  $verified
     */
    public function setEmailVerificationStatus($verified): void
    {
        if ($verified) {
            if (! $this->hasVerifiedEmail()) {
                $this->email_verified_at = $this->freshTimestamp();
            }
        } else {
            $this->email_verified_at = null;
        }
    }

    /**
     * Should verify email scope
     */
    public function scopeShouldVerifyEmail($query)
    {
        return $query->where('status', '!=', UserStatuses::BANNED);
    }

    /**
     * Approved users scope
     */
    public function scopeApproved($query)
    {
        return $query->where($this->getTable().'.status', UserStatuses::APPROVED);
    }

    /**
     * Active users scope
     */
    public function scopeActive($query)
    {
        return $query->approved()
            ->emailVerified()
            ->notLockedOut();
    }

    /**
     * Check if the user is active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->hasVerifiedEmail() &&
            $this->status == UserStatuses::APPROVED &&
            (! $this->is_locked_out);
    }

    /**
     * Get the max login attempts
     */
    public function maxLoginAttempts(): int
    {
        return config('auth.max_login_attempts');
    }

    /**
     * Check if is locked out
     */
    public function getIsLockedOutAttribute(): bool
    {
        return $this->login_attempts >= $this->maxLoginAttempts();
    }

    /**
     * Locked out users scope
     */
    public function scopeLockedOut($query)
    {
        return $query->where('login_attempts', '>=', $this->maxLoginAttempts());
    }

    /**
     * Not locked out users scope
     */
    public function scopeNotLockedOut($query)
    {
        return $query->whereNull('login_attempts')
            ->orWhere('login_attempts', '<', $this->maxLoginAttempts());
    }

    /**
     * Check if is approved
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status == UserStatuses::APPROVED;
    }

    /**
     * Check if is banned
     */
    public function getIsBannedAttribute(): bool
    {
        return $this->status == UserStatuses::BANNED;
    }

    /**
     * Check if is pending
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status == UserStatuses::PENDING;
    }

    /**
     * User visible
     */
    public function scopeUserVisible($query): mixed
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
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->is_approved;
    }

    /**
     * Get email verification redirect url
     */
    public function emailVerificationRedirectUrl(): string
    {
        return $this->homeUrl();
    }

    /**
     * Get initials attribute
     */
    public function getInitialsAttribute(): string
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
     */
    public function incrementLoginAttempts(): void
    {
        $this->login_attempts = ($this->login_attempts ?: 0) + 1;
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts(): void
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
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar')
            ->width(200)
            ->height(200)
            ->fit(Fit::Crop, 200, 200)
            ->keepOriginalImageFormat()
            ->performOnCollections('avatar');

        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400);
    }

    /**
     * Get the avatar url
     */
    public function getAvatarAttribute(): string
    {
        $avatar = $this->getFirstMediaUrl('avatar', 'avatar');

        return $avatar ?: asset(get_setting('default_avatar'));
    }

    /**
     * Get the provider for this user type
     */
    public function getProvider(): string
    {
        return $this->getTable();
    }

    /**
     * Get the list name
     */
    public function getListNameAttribute(): string
    {
        return __(':name (:email)', ['name' => $this->name, 'email' => $this->email]);
    }
}
