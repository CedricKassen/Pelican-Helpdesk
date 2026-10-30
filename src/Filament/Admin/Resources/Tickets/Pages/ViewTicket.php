<?php

namespace CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages;

use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\TicketResource;
use CedricKassen\Helpdesk\Filament\Components\Actions\AssignToMeAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\CloseAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\InternalNoteAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReopenAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReplyAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\On;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    #[On('ticket-updated')]
    public function refreshTicket(): void
    {
        $this->record->refresh();
    }

    protected function getHeaderActions(): array
    {
        return [
            ReplyAction::make(),
            InternalNoteAction::make(),
            AssignToMeAction::make(),
            CloseAction::make(),
            ReopenAction::make(),
            EditAction::make(),
        ];
    }
}
