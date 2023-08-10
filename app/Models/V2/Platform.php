<?php

namespace App\Models\V2;

use App\Models\V2\Business;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Platform extends Model
{
    use HasFactory;

    protected $table = 'platforms';
    protected $fillable = [
        'name',
        'url'
    ];
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}
