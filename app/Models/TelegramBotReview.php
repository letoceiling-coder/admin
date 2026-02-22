<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramBotReview extends Model
{
    protected $table = 'telegram_bot_reviews';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'author_name',
        'company',
        'rating',
        'text',
        'status',
        'telegram_user_id',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
