<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AmsForm extends Model
{
    protected $fillable = [
        'name',
        'descriptions',
        'filepath',
    ];

    /**
     * Get the file extension from filepath
     */
    public function getFileExtensionAttribute()
    {
        return pathinfo($this->filepath, PATHINFO_EXTENSION);
    }

    /**
     * Get the file size in human readable format
     */
    public function getFileSizeAttribute()
    {
        if (!$this->filepath || !Storage::disk('public')->exists($this->filepath)) {
            return 'N/A';
        }

        $bytes = Storage::disk('public')->size($this->filepath);

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get the original filename
     */
    public function getOriginalFilenameAttribute()
    {
        return basename($this->filepath);
    }

    /**
     * Check if file exists
     */
    public function fileExists()
    {
        return $this->filepath && Storage::disk('public')->exists($this->filepath);
    }

    /**
     * Delete the file when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($form) {
            if ($form->filepath && Storage::disk('public')->exists($form->filepath)) {
                Storage::disk('public')->delete($form->filepath);
            }
        });
    }
}
