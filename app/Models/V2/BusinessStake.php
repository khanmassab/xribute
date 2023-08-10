<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessStake extends Model
{
    use HasFactory;

    protected $table= 'share_holders';

    protected $fillable = [
        'business_id',
        'document',
        'is_registered',
        'registered_capital',
        'total_shares_in_business',
        'per_share_value',
        'share_owner'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
    
}