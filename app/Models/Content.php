<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guid',
        'name',
        'is_folder',
        'user_id',
        'parent_id',
        'path',
        'size',
        'extension',
    ];

    /**
     * Get the user who owns the content.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent content (folder).
     */
    public function parent()
    {
        return $this->belongsTo(Content::class, 'parent_id');
    }

    /**
     * Get child contents (files or folders).
     */
    public function children()
    {
        return $this->hasMany(Content::class, 'parent_id', 'id')->where('is_folder', true)->with('children');
    }

    public function allChildren()
    {
        // Fetch all children (both files and folders), including trashed content
        return $this->hasMany(Content::class, 'parent_id', 'id')->withTrashed();
    }

    /**
     * Check if the content is a folder.
     */
    public function isFolder()
    {
        return $this->is_folder;
    }

    /**
     * Get the access controls for the content.
     */
    public function accessControls() {
        return $this->hasMany(AccessControl::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->guid) {
                $model->guid = (string) Str::uuid();
            }
        });
    }
}
