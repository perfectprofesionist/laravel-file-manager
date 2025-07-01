<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrashLog extends Model
{
    use HasFactory;

    protected $table = 'trashlogs';

    protected $fillable = [
        'guid',
        'content_id',
        'user_id',
        'trashed_at',
        'deleted_at'
    ];

    // Define relationship to Content
    public function content()
    {
        return $this->belongsTo(Content::class)->withTrashed();
    }

    // Define relationship to User (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
