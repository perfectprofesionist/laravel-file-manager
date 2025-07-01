<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessControl extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'content_id',
        'access_type',
    ];

    /**
     * Relationships: User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationships: Content.
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
