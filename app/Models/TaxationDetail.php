<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxationDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'taxation_country',
        'tax_id',
        'country_id'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
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
