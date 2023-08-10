<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shareholder extends Model
{
    protected $table = 'shareholders';
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'no_of_shares',
        'username_or_email',
        'share_value',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}