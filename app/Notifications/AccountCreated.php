<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreated extends Notification
{
    public function __construct(public string $password) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your New Account Details')
            ->line('Your account has been created.')
            ->line('Your temporary password is: '.$this->password)
            ->line('Please change your password after logging in.')
            ->action('Go to Admin Panel', 'https://admin.sachsevent.com/admin');
    }
}
