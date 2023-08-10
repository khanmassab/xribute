<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_logo',
        'country',
        'business_name',
        'business_registration_proof',
        'legal_type',
        'business_registration_number',
        'business_registration_date',
        'business_city',
        'business_address',
        'relevant_tax_authority',
        'vat_number',
    ];

    public function user(){
        return $this->belongTo(User::class, 'id', 'user_id');
    }

    public function addressAndContact()
    {
        return $this->hasMany(AddressAndContact::class, 'business_id');
    }

    public function platforms()
    {
        return $this->hasMany(Platform::class, 'business_id');
    }

    public function businessStakes(){
        return $this->hasMany(BusinessStake::class, 'business_id');
    }

    public function shareholder(){
        return $this->hasMany(Shareholder::class, 'business_id');
    }

    public function management(){
        return $this->hasMany(Management::class, 'business_id');
    }

    public function businessBranches(){
        return $this->hasMany(BusinessBranch::class, 'business_id');
    }

    public function teamUsers(){
        return $this->hasMany(TeamUser::class, 'business_id');
    }

    public function teams(){
        return $this->hasMany(Team::class, 'business_id');
    }

    public function getTeamUsersCountAttribute() {
        return $this->teamUsers()->count();
    }
}

