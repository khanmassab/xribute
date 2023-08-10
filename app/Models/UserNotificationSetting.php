<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_notifications',
        'pop_up_notifications',
        'preview_notifications',
        'flash_notifications'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'show_notifications' => 'int',
        'pop_up_notifications' => 'int',
        'preview_notifications' => 'int',
        'flash_notifications' => 'int',
   ];
    /**
     * Get the user that owns the UserNotificationSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserDetail::class);
    }
}
