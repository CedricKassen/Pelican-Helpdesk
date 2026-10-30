<?php

namespace CedricKassen\Helpdesk\Filament\App\Resources\Tickets\RelationManagers;

use App\Filament\Components\Tables\Columns\DateTimeColumn;
use CedricKassen\Helpdesk\Filament\Components\AttachmentList;
use CedricKassen\Helpdesk\Filament\Concerns\HandlesInlineReply;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Models\TicketMessage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

/**
 * @method Ticket getOwnerRecord()
 */
class MessagesRelationManager extends RelationManager
{
    use HandlesInlineReply;

    protected static string $relationship = 'messages';

    // Re-render (and therefore re-query) the message list when a ticket action fires.
    #[On('ticket-updated')]
    public function refreshMessages(): void {}

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(trans_choice('helpdesk::tickets.message', 1))
            ->pluralModelLabel(trans_choice('helpdesk::tickets.message', 2))
            // The relation manager renders its own sticky chat header, so hide
            // the table's built-in heading (its card is flattened via CSS).
            ->heading('')
            ->paginated(false)
            ->defaultSort('created_at', 'asc')
            ->recordClasses(fn (TicketMessage $ticketMessage) => $this->messageRowClasses($ticketMessage))
            // Never expose internal staff notes to the ticket author.
            ->modifyQueryUsing(fn (Builder $query) => $query->where('is_internal', false))
            ->columns([
                Stack::make([
                    ViewColumn::make('message')
                        ->view('helpdesk::columns.message'),
                    TextColumn::make('attachment_links')
                        ->label('')
                        ->state(fn (TicketMessage $ticketMessage) => $this->renderAttachments($ticketMessage))
                        ->html()
                        ->placeholder(''),
                    Split::make([
                        TextColumn::make('author.username')
                            ->grow(false)
                            ->placeholder(trans('helpdesk::tickets.unknown'))
                            ->icon('tabler-user'),
                        DateTimeColumn::make('created_at')
                            ->grow(false)
                            ->since(),
                        TextColumn::make('is_staff')
                            ->grow(false)
                            ->badge()
                            ->color('info')
                            ->icon('tabler-headset')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->author_id && $ticketMessage->author_id !== $this->getOwnerRecord()->author_id ? trans('helpdesk::tickets.staff') : null),
                    ]),
                ])->space(3),
            ]);
    }

    public function render(): View
    {
        return view('helpdesk::relation-managers.messages');
    }

    protected function renderAttachments(TicketMessage $ticketMessage): ?string
    {
        return AttachmentList::render($ticketMessage->attachments);
    }
}
