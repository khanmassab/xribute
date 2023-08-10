<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model {
    protected $fillable = ['room_type', 'user_ids', 'type'];

    /**
     * Get the messages of a chat room
     */
    public function messages(){
        return $this->hasMany(Message::class, 'chat_room_id')->with('sender');
    }
}
