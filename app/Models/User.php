<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
// use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $model->id = Str::uuid();
    //     });
    // }
    protected $fillable = [
        'date_format_id',
        'is_active',
        'email',
        'middle_name',
        'first_name',
        'last_name',
        'phone',
        'surname',
        'is_agreed_to_terms',
        'is_human',
        'password',
        'remember_me',
        'account_type_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "email_verified_at",
        "is_human",
        "is_agreed_to_terms",
        "is_active",
        'password',
        'remember_token',
        'id',
        'created_at',
        'deleted_at',
        'updated_at',
    ];



    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function detail()
    {
        return $this->hasOne('App\Models\UserDetail');
    }

    // public function business()
    // {
    //     return $this->hasOne('App\Models\V2\Business');
    // }

    public function oauth()
    {
        return $this->hasOne(OAuth::class, 'user_id');
    }

    public function address()
    {
        return $this->hasOne('App\Models\UserAddress');
    }

    public function userNotificationSetting()
    {
        return $this->hasOne('App\Models\UserNotificationSetting');
    }

    public function nationality()
    {
        return $this->hasMany('App\Models\NationalityDetail');
    }

    public function taxation()
    {
        return $this->hasMany('App\Models\TaxationDetail');
    }


    public function dateFormat(){
        return $this->belongsTo(DateFormat::class, 'date_format_id','id');
    }


    public function userDetailData(){
        return $this->belongsTo(UserDetail::class, 'id','user_id');
    }

    public function nationalityDetailData(){
        return $this->belongsTo(NationalityDetail::class, 'id','user_id');
    }

    public function taxationDetailData(){
        return $this->belongsTo(TaxationDetail::class, 'id','user_id');
    }

    public function userAddressData(){
        return $this->belongsTo(UserAddress::class, 'id','user_id');
    }

    public function userNotificationSettingData(){
        return $this->belongsTo(UserNotificationSetting::class, 'id','user_id');
    }

    public function userContactDetailData(){
        return $this->belongsTo(UserContactDetail::class, 'id','user_id');
    }

    public function accountTypes()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id', 'id');
    }

    public function businessProfile(){
        return $this->belongsTo(BusinessProfile::class, 'id','user_id');
    }

    public function business(){
        return $this->hasMany('App\Models\V2\Business', 'user_id','id');
    }
}

