<?php

namespace CedricKassen\Helpdesk\Notifications;

use App\Models\User;
use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages\ViewTicket;
use CedricKassen\Helpdesk\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket, public TicketStatus $status, public ?string $answer = null) {}

    /** @return string[] */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $locale = $notifiable->language ?? 'en';

        $key = $this->status === TicketStatus::Closed ? 'closed' : 'reopened';

        $mailMessage = (new MailMessage())
            ->subject(trans("helpdesk::tickets.mail.{$key}_subject", ['id' => $this->ticket->id, 'title' => $this->ticket->title], $locale))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->username], $locale))
            ->line(trans("helpdesk::tickets.mail.{$key}_line", ['title' => $this->ticket->title], $locale));

        if ($this->answer) {
            $mailMessage->line('> ' . Str::limit(strip_tags($this->answer), 500));
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
