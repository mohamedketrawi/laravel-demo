<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = "users";

    public $timestamps = false;

    public function image(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Image::class, 'image_id');
    }
}
