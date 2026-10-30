<?php

namespace CedricKassen\Helpdesk\Filament\Components\Actions;

use CedricKassen\Helpdesk\Models\Ticket;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AssignToMeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'assign_to_me';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()?->can('update ticket', $ticket));

        $this->hidden(fn (Ticket $ticket) => $ticket->assigned_user_id === auth()->user()?->id);

        $this->label(trans('helpdesk::tickets.assign_to_me'));

        $this->icon('tabler-user-share');

        $this->color('primary');

        $this->requiresConfirmation(false);

        $this->action(function (Ticket $ticket, $livewire) {
            $ticket->assignTo(auth()->user());

            $livewire->dispatch('ticket-updated');

            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.assigned_to_you'))
                ->success()
                ->send();
        });
    }
}
