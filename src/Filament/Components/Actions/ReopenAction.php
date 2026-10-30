<?php

namespace CedricKassen\Helpdesk\Filament\Components\Actions;

use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ReopenAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reopen';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->visible(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed);

        $this->label(trans('helpdesk::tickets.reopen'));

        $this->icon('tabler-rotate');

        $this->color('warning');

        $this->requiresConfirmation();

        $this->action(function (Ticket $ticket, $livewire) {
            $ticket->reopen(auth()->user());

            $livewire->dispatch('ticket-updated');

            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.reopened_success'))
                ->success()
                ->send();
        });
    }
}
