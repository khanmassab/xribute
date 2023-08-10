<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusinessCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id','user_id'
    ];
    public $timestamps = true;

    public function businessProfiles()
    {
        return $this->belongsToMany(BusinessProfile::class);
    }
}
