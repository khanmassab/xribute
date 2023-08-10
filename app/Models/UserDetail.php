<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table= 'user_details';
    use HasFactory;
    
    protected $fillable = [
        'title',
        'user_id',
        'prefix_id',
        'title_id',
        'language_id',
        'gender_id',
        'gender',
        'birth_name',
        'date_of_birth',
        'place_of_birth_id',
        'place_of_birth',
        'country',
        'city',
        'profile_image',
        'cover_image',
        'country_id'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
    ];

    public function userGender()
    {
        return $this->belongsTo(UserGender::class, 'gender_id', 'id');
    }

    public function userLanguage()
    {
        return $this->belongsTo(UserLanguage::class, 'language_id', 'id');
    }
    
    public function userPrefix()
    {
        return $this->belongsTo(UserPrefix::class, 'prefix_id', 'id');
    }
    public function userBirth()
    {
        return $this->belongsTo(Country::class, 'place_of_birth_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function Country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }
}

