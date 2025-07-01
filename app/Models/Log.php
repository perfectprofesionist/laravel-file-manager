<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['table_name', 'operation', 'old_data', 'new_data', 'user_id'];

    // Log belongs to a user (who performed the action)
    public function user() {
        return $this->belongsTo(User::class);
    }
}
