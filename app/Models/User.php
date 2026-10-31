<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, QuranBookmark> $quranBookmarks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, QuranKhatamProgram> $khatamPrograms
 * @property-read QuranBookmark|null $lastQuranRead
 */
class User extends Authenticatable
{
    public function hasAttendanceHistory(bool $currentRead = false): bool
    {
        $queries = [
            DB::table('activity_attendances')->where('user_id', $this->id),
            DB::table('activity_attendance_sessions')->where('opened_by', $this->id),
            DB::table('attendance_reward_periods')->where('created_by', $this->id)->orWhere('finalized_by', $this->id),
            DB::table('attendance_reward_recipients')->where('user_id', $this->id)
                ->orWhere('status_changed_by', $this->id)->orWhere('delivered_by', $this->id),
        ];
        foreach ($queries as $query) {
            // Locking reads see rows committed while deletion waited for the account lock.
            if (($currentRead ? $query->lockForUpdate() : $query)->first(['id'])) {
                return true;
            }
        }

        return false;
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'avatar',
        'address',
        'gender',
        'birth_date',
        'password',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_remembered_at' => 'datetime',
        ];
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function adzanSetting()
    {
        return $this->hasOne(UserAdzanSetting::class);
    }

    public function revokeAccess(): void
    {
        $this->forceFill(['remember_token' => null])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $this->id)
                ->delete();
        }
    }

    public function favoriteDuas()
    {
        return $this->belongsToMany(Doa::class, 'user_doa_favorites', 'user_id', 'doa_id')
            ->withTimestamps();
    }

    public function quranBookmarks()
    {
        return $this->hasMany(QuranBookmark::class);
    }

    public function khatamPrograms()
    {
        return $this->hasMany(QuranKhatamProgram::class);
    }

    public function lastQuranRead()
    {
        return $this->hasOne(QuranBookmark::class)->where('is_last_read', true)->latestOfMany();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1B6B3A&color=fff';
    }

    public function isJamaah(): bool
    {
        return $this->hasRole('jamaah');
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([
            'super_admin',
            'admin_operasional',
            'admin_keuangan',
            'admin_media_it',
        ]);
    }

    public function requiresTwoFactor(): bool
    {
        return $this->isAdmin();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at) && !is_null($this->two_factor_secret);
    }

    public function hasGoogleAuth(): bool
    {
        return !is_null($this->google_id);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function getAdminPanelUrl(): string
    {
        if ($this->hasRole('super_admin')) return route('super-admin.dashboard');
        if ($this->hasRole('admin_operasional')) return route('admin.operasional.dashboard');
        if ($this->hasRole('admin_keuangan')) return route('admin.keuangan.dashboard');
        if ($this->hasRole('admin_media_it')) return route('admin.media-it.dashboard');
        return route('user.profile');
    }
}
