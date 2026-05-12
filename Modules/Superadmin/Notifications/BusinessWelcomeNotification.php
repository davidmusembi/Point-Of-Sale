<?php

namespace Modules\Superadmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BusinessWelcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $businessName,
        protected string $loginUrl,
        protected string $username,
        protected string $password
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Welcome to {$this->businessName} — Your Login Credentials")
            ->greeting('Hello ' . ($notifiable->first_name ?? $notifiable->username) . ',')
            ->line("Your business **{$this->businessName}** has been set up on " . config('app.name') . '.')
            ->line('Here are your one-time login credentials:')
            ->line("**Username:** {$this->username}")
            ->line("**Temporary Password:** {$this->password}")
            ->action('Login Now', $this->loginUrl)
            ->line('Please change your password after your first login.')
            ->salutation('— ' . config('app.name') . ' Team');
    }
}
