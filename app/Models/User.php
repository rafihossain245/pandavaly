<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Log\VendorLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use SoftDeletes;

    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'is_super_admin',
        'email_verified_at',
        'remember_token',
        'phone',
        'address',
        'contact_person',
        'status', // Added status field
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username) && !empty($user->name)) {
                $user->username = static::generateUniqueUsername($user->name);
            }
        });
    }

    protected static function generateUniqueUsername($name)
    {
        $base = Str::slug($name);
        $username = $base;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $base . '-' . $counter++;
        }

        return $username;
    }

    public function logs()
    {
        return $this->hasMany(VendorLog::class, 'vendor_id');
    }
}
