<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    use HasFactory;
    protected $table = 'images';
    protected $fillable = [
        'user_id',
        'image'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }
}
