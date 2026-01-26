<?php

namespace App\Notifications;

use App\Models\SubscriptionApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewSubscriptionApplicationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SubscriptionApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Новая заявка на подписку',
            'message' => sprintf('%s — %s', $this->application->domain, $this->application->email),
            'application_id' => $this->application->id,
        ];
    }
}
