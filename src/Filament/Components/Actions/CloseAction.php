<?php

namespace CedricKassen\Helpdesk\Filament\Components\Actions;

use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Filament\Components\AttachmentUpload;
use CedricKassen\Helpdesk\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;

class CloseAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'close';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->hidden(fn (Ticket $ticket) => $ticket->status === TicketStatus::Closed);

        $this->label(trans('helpdesk::tickets.close'));

        $this->icon('tabler-circle-check');

        $this->color('danger');

        $this->modalHeading(trans('helpdesk::tickets.close'));

        // The closing message is only offered to staff; regular users just close the ticket.
        $this->schema(function (Ticket $ticket): array {
            if (!auth()->user()?->can('update ticket', $ticket)) {
                return [];
            }

            return [
                MarkdownEditor::make('answer')
                    ->label(trans('helpdesk::tickets.closing_message'))
                    ->nullable(),
                AttachmentUpload::make(),
            ];
        });

        $this->action(function (Ticket $ticket, array $data, $livewire) {
            $ticket->close(auth()->user(), $data['answer'] ?? null, $data['attachments'] ?? []);

            $livewire->dispatch('ticket-updated');

            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.closed_success'))
                ->success()
                ->send();
        });
    }
}
