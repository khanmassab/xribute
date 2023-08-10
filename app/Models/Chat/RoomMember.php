<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class RoomMember extends Model {
    public $timestamps = false;
    protected $guarded = [];
//    protected $fillable = ['chat_room_id', 'user_ids'];

    /**
     * Get the sender of the message
     */
    public function chatRoom(){
        return $this->belongsTo(ChatRoom::class);
    }

    public function getUserIdsAttribute($value){
        return unserialize($value);
    }

    public function scopeMembers($query){
        return $query->where('active', 1);
    }
}
