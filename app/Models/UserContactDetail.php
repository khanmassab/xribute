<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactDetail extends Model
{
    protected $table = 'user_contact_details';
    protected $primaryKey = 'id';
    // protected $foreignKey = 'user_id';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'telephone',
        'fax',
        'whatsapp_number',
        'additional_phone',
        'additional_email',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
