<?php

namespace CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\RelationManagers;

use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use CedricKassen\Helpdesk\Filament\Components\AttachmentList;
use CedricKassen\Helpdesk\Filament\Concerns\HandlesInlineReply;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Models\TicketMessage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
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
                            ->icon('tabler-user')
                            ->url(fn (TicketMessage $ticketMessage) => $ticketMessage->author && auth()->user()->can('update user', $ticketMessage->author) ? EditUser::getUrl(['record' => $ticketMessage->author], panel: 'admin') : null),
                        DateTimeColumn::make('created_at')
                            ->grow(false)
                            ->since(),
                        TextColumn::make('is_internal')
                            ->grow(false)
                            ->badge()
                            ->color('warning')
                            ->icon('tabler-notes')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->is_internal ? trans('helpdesk::tickets.internal') : null),
                        TextColumn::make('is_author')
                            ->grow(false)
                            ->badge()
                            ->color('success')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->author_id && $ticketMessage->author_id === $this->getOwnerRecord()->author_id ? trans('helpdesk::tickets.author') : null),
                        TextColumn::make('is_assigned')
                            ->grow(false)
                            ->badge()
                            ->color('info')
                            ->icon('tabler-headset')
                            ->state(fn (TicketMessage $ticketMessage) => $ticketMessage->author_id && $ticketMessage->author_id === $this->getOwnerRecord()->assigned_user_id ? trans('helpdesk::tickets.staff') : null),
                    ]),
                ])->space(3),
            ])
            ->recordActions([
                EditAction::make()
                    ->authorize(fn (): bool => (bool) auth()->user()?->can('update ticket', $this->getOwnerRecord()))
                    ->modalHeading(trans('helpdesk::tickets.edit_message'))
                    ->schema([
                        MarkdownEditor::make('message')
                            ->label(trans_choice('helpdesk::tickets.message', 1))
                            ->nullable(),
                        Toggle::make('is_internal')
                            ->label(trans('helpdesk::tickets.internal_note')),
                    ]),
                DeleteAction::make()
                    ->authorize(fn (): bool => (bool) auth()->user()?->can('update ticket', $this->getOwnerRecord())),
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
