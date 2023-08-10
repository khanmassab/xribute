<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessBranch extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'branch_address',
        'address',
        'building_no',
        'town_city',
        'postal_code',
        'country',
        'website',
        'branch_contact',
        'mobile',
        'telefone',
        'fax',
        'email',
        'locations',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}
