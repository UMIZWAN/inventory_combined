<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'asset_access_level_id',
        'marketing_access_level_id',
        'is_active',
        'is_accessible',
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
            'is_active' => 'boolean',
            'is_accesible' => 'boolean',
        ];
    }

    /* =========================
       Relationships
    ========================= */

    public function accessLevel()
    {
        return $this->belongsTo(AssetAccessLevel::class, 'asset_access_level_id');
    }

    public function marketingAccessLevel()
    {
        return $this->belongsTo(MarketingAccessLevel::class, 'marketing_access_level_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->withTimestamps();
    }

    /* =========================
    Mutators
    Automatically make name & username uppercase
    Remove spaces from username
    ========================= */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtoupper(str_replace(' ', '', $value));
    }
}
