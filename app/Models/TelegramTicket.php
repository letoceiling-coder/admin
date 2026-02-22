<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramTicket extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $table = 'telegram_tickets';

    protected $fillable = [
        'telegram_user_id',
        'username',
        'subject',
        'message',
        'attachment_file_id',
        'status',
    ];
}
