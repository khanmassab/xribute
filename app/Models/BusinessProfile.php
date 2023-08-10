<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    use HasFactory;
//    protected $fillable = [
//        'serial_number', 'user_id', 'name', 'business_age', 'time_response', 'pricing', 'bio'
//    ];
    public $timestamps = true;
    protected $guarded = [];
    public function businessCategories(){
        return $this->belongsToMany(BusinessCategory::class);
    }

    public function user(){
    return $this->belongsTo(User::class);
    }

    public function business_category(){
        return $this->belongsTo(Category::class, 'business_category_id','id')->where(['is_active' => 1]);
    }
    public function business_city(){
        return $this->belongsTo(cityList::class, 'business_city_id','id')->where(['is_active' => 1]);
    }
    public function business_country(){
        return $this->belongsTo(Country::class, 'business_country_id','id');
    }
    public function texationCountry(){
        return $this->belongsTo(Country::class, 'taxation_country_id','id');
    }
    public function BusinessAssignedCategories(){
        return $this->hasMany(BusinessAssignedCategories::class, 'business_id','id')->where(['is_active' => 1]);
    }
}
