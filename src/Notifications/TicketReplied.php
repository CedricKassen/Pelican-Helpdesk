<?php

namespace CedricKassen\Helpdesk\Notifications;

use App\Models\User;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages\ViewTicket;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public TicketMessage $message) {}

    /** @return string[] */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $locale = $notifiable->language ?? 'en';

        $excerpt = Str::limit(strip_tags((string) $this->message->message), 500);

        $mailMessage = (new MailMessage())
            ->subject(trans('helpdesk::tickets.mail.reply_subject', ['id' => $this->ticket->id, 'title' => $this->ticket->title], $locale))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->username], $locale))
            ->line(trans('helpdesk::tickets.mail.reply_line', ['title' => $this->ticket->title], $locale));

        if ($excerpt !== '') {
            $mailMessage->line('> ' . $excerpt);
        }

        if ($url = $this->ticketUrl()) {
            $mailMessage->action(trans('helpdesk::tickets.mail.view_action', [], $locale), $url);
        }

        return $mailMessage;
    }

    protected function ticketUrl(): ?string
    {
        return rescue(fn () => ViewTicket::getUrl(['record' => $this->ticket->id], panel: 'app'), null, false);
    }
}
