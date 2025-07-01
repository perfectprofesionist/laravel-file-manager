<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileExtention extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['file_type_id', 'name', 'svg_path'];

    /**
     * Get the file type that owns the file extension.
     */
    public function fileType()
    {
        return $this->belongsTo(FileType::class);
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
