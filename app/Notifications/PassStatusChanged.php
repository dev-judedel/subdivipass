<?php

namespace App\Notifications;

use App\Models\Pass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PassStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Pass $pass,
        public string $fromStatus,
        public string $toStatus,
        public ?string $message = null
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pass status updated')
            ->line("Pass {$this->pass->pass_number} changed from {$this->fromStatus} to {$this->toStatus}.")
            ->line($this->message ?? '')
            ->action('View pass', url()->route('passes.show', $this->pass->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pass_id' => $this->pass->id,
            'pass_number' => $this->pass->pass_number,
            'from_status' => $this->fromStatus,
            'to_status' => $this->toStatus,
            'message' => $this->message,
        ];
    }
}
