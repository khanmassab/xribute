<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Management extends Model
{
    use HasFactory;
    protected $table = 'managers';
    protected $guarded=[
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}
