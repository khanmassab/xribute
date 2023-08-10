<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'administrator_id',
        'name',
        'image',
        'role',
        'access',
        'visiblity',
        'weekdays',
        'starttime',
        'endtime',
    ];

    protected $cast = ['visiblity' => 'boolean'];

    public function business(){
        return $this->belongTo(User::class, 'id', 'business_id');
    }

    public function teamUsers(){
        return $this->hasMany(TeamUser::class, 'team_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->using('team_user_role')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

}
