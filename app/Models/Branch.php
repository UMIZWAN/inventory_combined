<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = "branches";
    protected $fillable = [
        'branch_name',
        'code',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withTimestamps();
    }

    /**
     * UPPERCASE AND SPACE MUTATORS
     * @param mixed $value
     * @return void
     */
    public function setBranchNameAttribute($value)
    {
        $this->attributes['branch_name'] = strtoupper($value);
    }
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper(str_replace(' ', '', $value));
    }
}
