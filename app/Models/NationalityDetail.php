<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NationalityDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nationality',
        'name_on_id',
        'national_id',
        'issue_date',
        'expiry_date',
        'country',
        'country_id'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expiry_date' => 'date:Y-m-d',
        'issue_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }
    
    public function Country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }
}
