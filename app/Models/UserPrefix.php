<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrefix extends Model
{
    use HasFactory;
    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class, 'user_id', 'id');
    }
}
