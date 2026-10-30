<?php

namespace CedricKassen\Helpdesk\Filament\Components\Actions;

use CedricKassen\Helpdesk\Filament\Components\AttachmentUpload;
use CedricKassen\Helpdesk\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;

class InternalNoteAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'internal_note';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize(fn (Ticket $ticket) => auth()->user()?->can('update ticket', $ticket));

        $this->label(trans('helpdesk::tickets.internal_note'));

        $this->icon('tabler-notes');

        $this->color('warning');

        $this->modalHeading(trans('helpdesk::tickets.internal_note'));

        $this->schema([
            MarkdownEditor::make('message')
                ->label(trans_choice('helpdesk::tickets.message', 1))
                ->required(),
            AttachmentUpload::make(),
        ]);

        $this->action(function (Ticket $ticket, array $data, $livewire) {
            $ticket->reply($data['message'], true, auth()->user(), $data['attachments'] ?? []);

            $livewire->dispatch('ticket-updated');

            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.note_added'))
                ->success()
                ->send();
        });
    }
}
