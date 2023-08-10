<?php

namespace App\Models\Chat;

use App\Models\BusinessProfile;
use App\Models\MessageFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $guarded = [];
    public function sender(){
        return $this->hasOne(BusinessProfile::class, 'id', 'sender_id');
    }
    public function files(){
        return $this->belongsTo(MessageFile::class, 'id', 'message_id');
    }

    public function receivers(){
        return $this->hasMany(Receiver::class);
    }
}
