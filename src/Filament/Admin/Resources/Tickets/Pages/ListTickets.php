<?php

namespace CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages;

use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\TicketResource;
use CedricKassen\Helpdesk\Models\Ticket;
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
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'my' => Tab::make(trans('helpdesk::tickets.assigned_to_me'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value)->where('assigned_user_id', auth()->user()->id))
                ->badge(fn () => Ticket::whereNot('status', TicketStatus::Closed->value)->where('assigned_user_id', auth()->user()->id)->count()),

            'open' => Tab::make(trans('helpdesk::tickets.open'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::whereNot('status', TicketStatus::Closed->value)->count()),

            'closed' => Tab::make(trans('helpdesk::tickets.closed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', TicketStatus::Closed->value))
                ->badge(fn () => Ticket::where('status', TicketStatus::Closed->value)->count()),

            'all' => Tab::make(trans('helpdesk::tickets.all'))
                ->badge(fn () => Ticket::count()),
        ];
    }
}
