<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileNationality extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'country_id',
        'proof_type',
        'nationality_proof'
    ];
    
    protected $table = 'profile_nationality';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
