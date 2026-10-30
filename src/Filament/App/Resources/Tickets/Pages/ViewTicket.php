<?php

namespace CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages;

use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\TicketResource;
use CedricKassen\Helpdesk\Filament\Components\Actions\CloseAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReopenAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReplyAction;
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
            CloseAction::make(),
            ReopenAction::make(),
        ];
    }
}
