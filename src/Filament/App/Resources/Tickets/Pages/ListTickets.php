<?php

namespace CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages;

use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\TicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(trans('helpdesk::tickets.create')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'open' => Tab::make(trans('helpdesk::tickets.open'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value)),

            'closed' => Tab::make(trans('helpdesk::tickets.closed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::Closed->value)),

            'all' => Tab::make(trans('helpdesk::tickets.all')),
        ];
    }
}
