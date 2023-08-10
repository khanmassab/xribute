<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationCode extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $guarded = [];
    protected $casts = [
        'user_id' => 'int',
    ];
}
