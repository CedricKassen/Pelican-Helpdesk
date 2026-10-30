<?php

namespace CedricKassen\Helpdesk\Services;

use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages\ViewTicket;
use CedricKassen\Helpdesk\Jobs\SendTicketWebhook;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Models\TicketMessage;
use CedricKassen\Helpdesk\Support\TicketOptions;
use Illuminate\Support\Str;

class TicketWebhookService
{
    public function ticketCreated(Ticket $ticket): void
    {
        $url = $this->url();

        if (!$url) {
            return;
        }

        SendTicketWebhook::dispatch($url, $this->payload($url, 'ticket.created', $ticket, $ticket->description));
    }

    public function ticketReplied(TicketMessage $message): void
    {
        $url = $this->url();

        if (!$url) {
            return;
        }

        $message->loadMissing('ticket');

        if (!$message->ticket) {
            return;
        }

        SendTicketWebhook::dispatch($url, $this->payload($url, 'ticket.reply', $message->ticket, $message->message, $message));
    }

    protected function url(): ?string
    {
        $url = config('helpdesk.webhook_url');

        return is_string($url) && trim($url) !== '' ? trim($url) : null;
    }

    protected function type(string $url): string
    {
        $configured = config('helpdesk.webhook_type');

        if (in_array($configured, ['discord', 'generic'], true)) {
            return $configured;
        }

        $host = (string) parse_url($url, PHP_URL_HOST);

        return Str::contains($host, ['discord.com', 'discordapp.com']) ? 'discord' : 'generic';
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(string $url, string $event, Ticket $ticket, ?string $body, ?TicketMessage $message = null): array
    {
        $adminUrl = rescue(fn () => ViewTicket::getUrl(['record' => $ticket->id], panel: 'admin'), null, false);
        $excerpt = $body ? Str::limit(strip_tags($body), 500) : null;

        if ($this->type($url) === 'discord') {
            return $this->discordPayload($event, $ticket, $excerpt, $adminUrl, $message);
        }

        return [
            'event' => $event,
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'status' => $ticket->status->value,
                'server_id' => $ticket->server_id,
                'author' => $ticket->author?->username,
                'url' => $adminUrl,
            ],
            'message' => $message ? [
                'id' => $message->id,
                'author' => $message->author?->username,
                'excerpt' => $excerpt,
            ] : null,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function discordPayload(string $event, Ticket $ticket, ?string $excerpt, ?string $adminUrl, ?TicketMessage $message): array
    {
        $titleKey = $event === 'ticket.created' ? 'webhook.created_title' : 'webhook.reply_title';

        $fields = [
            ['name' => trans('helpdesk::tickets.created_by'), 'value' => $ticket->author?->username ?? trans('helpdesk::tickets.unknown'), 'inline' => true],
            ['name' => trans('helpdesk::tickets.server'), 'value' => $ticket->server?->name ?? trans('helpdesk::tickets.no_server'), 'inline' => true],
            ['name' => trans('helpdesk::tickets.category'), 'value' => TicketOptions::categoryLabel($ticket->category), 'inline' => true],
            ['name' => trans('helpdesk::tickets.priority'), 'value' => TicketOptions::priorityLabel($ticket->priority), 'inline' => true],
            ['name' => trans('helpdesk::tickets.status'), 'value' => $ticket->status->getLabel(), 'inline' => true],
        ];

        if ($message && $message->author) {
            $fields[] = ['name' => trans('helpdesk::tickets.replied_by'), 'value' => $message->author->username, 'inline' => true];
        }

        return [
            'embeds' => [
                [
                    'title' => trans("helpdesk::tickets.$titleKey", ['id' => $ticket->id, 'title' => $ticket->title]),
                    'url' => $adminUrl,
                    'description' => $excerpt,
                    'color' => $event === 'ticket.created' ? 3447003 : 15844367,
                    'fields' => $fields,
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];
    }
}
