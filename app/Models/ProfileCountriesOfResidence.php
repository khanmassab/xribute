<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileCountriesOfResidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country_id',
        'proof_type',
        'residence_proof'
    ];
    
    protected $table = 'profile_countries_of_residence';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
