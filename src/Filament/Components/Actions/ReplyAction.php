<?php

namespace CedricKassen\Helpdesk\Filament\Components\Actions;

use CedricKassen\Helpdesk\Filament\Components\AttachmentUpload;
use CedricKassen\Helpdesk\Models\Ticket;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;

class ReplyAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reply';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('helpdesk::tickets.reply'));

        $this->icon('tabler-message-reply');

        $this->color('primary');

        $this->modalHeading(trans('helpdesk::tickets.reply'));

        $this->schema([
            MarkdownEditor::make('message')
                ->label(trans_choice('helpdesk::tickets.message', 1))
                ->required(),
            AttachmentUpload::make(),
        ]);

        $this->action(function (Ticket $ticket, array $data, $livewire) {
            $ticket->reply($data['message'], false, auth()->user(), $data['attachments'] ?? []);

            $livewire->dispatch('ticket-updated');

            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.reply_sent'))
                ->success()
                ->send();
        });
    }
}
