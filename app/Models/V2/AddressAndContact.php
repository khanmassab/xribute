<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressAndContact extends Model
{
    use HasFactory;
    protected $table = 'address_and_contact';
    protected $fillable = [
        'business_id',
        'address_label',
        'address',
        'building_no',
        'town_city',
        'postal_code',
        'country',
        'website',
        'contact_label',
        'mobile',
        'telefone',
        'fax',
        'email'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

}
