<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'email',
        'business_id',
        'status',
        'token',
        'team_id',
        'picture'
    ];

    public function teams(){
        return $this->belongsTo(Team::class, 'team_id','id');
    }

    public function business(){
        return $this->belongsTo(Business::class, 'business_id','id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id', 'role_id');
    }
}
