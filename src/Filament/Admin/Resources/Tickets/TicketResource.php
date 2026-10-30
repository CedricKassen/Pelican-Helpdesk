<?php

namespace CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets;

use App\Filament\Admin\Resources\Servers\Pages\EditServer;
use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Components\Tables\Columns\DateTimeColumn;
use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages\CreateTicket;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages\EditTicket;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages\ListTickets;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\Pages\ViewTicket;
use CedricKassen\Helpdesk\Filament\Admin\Resources\Tickets\RelationManagers\MessagesRelationManager;
use CedricKassen\Helpdesk\Filament\Components\Actions\AssignToMeAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\CloseAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReopenAction;
use CedricKassen\Helpdesk\Filament\Components\Actions\ReplyAction;
use CedricKassen\Helpdesk\Filament\Components\AttachmentList;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Support\TicketOptions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Markdown;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-ticket';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 2);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Ticket::whereNot('status', TicketStatus::Closed->value)->count() ?: null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('title')
                    ->label(trans_choice('helpdesk::tickets.title', 1))
                    ->description(fn (Ticket $ticket) => Markdown::inline($ticket->description ?? ''))
                    ->sortable()
                    ->searchable()
                    ->grow(),
                TextColumn::make('category')
                    ->label(trans('helpdesk::tickets.category'))
                    ->badge()
                    ->color(fn ($state) => TicketOptions::categoryColor($state))
                    ->formatStateUsing(fn ($state) => TicketOptions::categoryLabel($state))
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(trans('helpdesk::tickets.priority'))
                    ->badge()
                    ->color(fn ($state) => TicketOptions::priorityColor($state))
                    ->formatStateUsing(fn ($state) => TicketOptions::priorityLabel($state))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(trans('helpdesk::tickets.status'))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('assignedUser.username')
                    ->label(trans('helpdesk::tickets.assigned_to'))
                    ->icon('tabler-user')
                    ->placeholder(trans('helpdesk::tickets.noone'))
                    ->url(fn (Ticket $ticket) => $ticket->assignedUser && auth()->user()->can('update user', $ticket->assignedUser) ? EditUser::getUrl(['record' => $ticket->assignedUser]) : null)
                    ->toggleable(),
                TextColumn::make('server.name')
                    ->label(trans('helpdesk::tickets.server'))
                    ->icon('tabler-brand-docker')
                    ->placeholder(trans('helpdesk::tickets.no_server'))
                    ->url(fn (Ticket $ticket) => $ticket->server && auth()->user()->can('update server', $ticket->server) ? EditServer::getUrl(['record' => $ticket->server]) : null)
                    ->toggleable(),
                TextColumn::make('author.username')
                    ->label(trans('helpdesk::tickets.created_by'))
                    ->icon('tabler-user')
                    ->placeholder(trans('helpdesk::tickets.unknown'))
                    ->url(fn (Ticket $ticket) => $ticket->author && auth()->user()->can('update user', $ticket->author) ? EditUser::getUrl(['record' => $ticket->author]) : null)
                    ->toggleable(),
                DateTimeColumn::make('last_reply_at')
                    ->label(trans('helpdesk::tickets.last_reply'))
                    ->since()
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
                DateTimeColumn::make('created_at')
                    ->label(trans('helpdesk::tickets.created_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                ReplyAction::make(),
                AssignToMeAction::make(),
                CloseAction::make(),
                ReopenAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('last_reply_at', 'desc')
            ->groups([
                Group::make('category')
                    ->label(trans('helpdesk::tickets.category')),
                Group::make('priority')
                    ->label(trans('helpdesk::tickets.priority')),
                Group::make('status')
                    ->label(trans('helpdesk::tickets.status')),
                Group::make('server.name')
                    ->label(trans('helpdesk::tickets.server')),
                Group::make('author.username')
                    ->label(trans('helpdesk::tickets.created_by')),
            ])
            ->emptyStateIcon('tabler-ticket')
            ->emptyStateDescription('')
            ->emptyStateHeading(trans('helpdesk::tickets.no_tickets'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(trans_choice('helpdesk::tickets.title', 1))
                    ->required()
                    ->columnSpanFull(),
                Select::make('category')
                    ->label(trans('helpdesk::tickets.category'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketOptions::categoryOptions())
                    ->default(TicketOptions::defaultCategory()),
                Select::make('priority')
                    ->label(trans('helpdesk::tickets.priority'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketOptions::priorityOptions())
                    ->default(TicketOptions::defaultPriority()),
                Select::make('status')
                    ->label(trans('helpdesk::tickets.status'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketStatus::class)
                    ->default(TicketStatus::Open),
                Select::make('server_id')
                    ->label(trans('helpdesk::tickets.server'))
                    ->placeholder(trans('helpdesk::tickets.no_server'))
                    ->searchable()
                    ->preload()
                    ->relationship('server', 'name'),
                Select::make('author_id')
                    ->label(trans('helpdesk::tickets.created_by'))
                    ->searchable()
                    ->preload()
                    ->relationship('author', 'username'),
                MarkdownEditor::make('description')
                    ->label(trans('helpdesk::tickets.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 2, 'lg' => 4])
                    ->schema([
                        TextEntry::make('title')
                            ->label(trans_choice('helpdesk::tickets.title', 1))
                            ->columnSpanFull(),
                        TextEntry::make('category')
                            ->label(trans('helpdesk::tickets.category'))
                            ->badge()
                            ->color(fn ($state) => TicketOptions::categoryColor($state))
                            ->formatStateUsing(fn ($state) => TicketOptions::categoryLabel($state)),
                        TextEntry::make('priority')
                            ->label(trans('helpdesk::tickets.priority'))
                            ->badge()
                            ->color(fn ($state) => TicketOptions::priorityColor($state))
                            ->formatStateUsing(fn ($state) => TicketOptions::priorityLabel($state)),
                        TextEntry::make('status')
                            ->label(trans('helpdesk::tickets.status'))
                            ->badge(),
                        TextEntry::make('assignedUser.username')
                            ->label(trans('helpdesk::tickets.assigned_to'))
                            ->icon('tabler-user')
                            ->placeholder(trans('helpdesk::tickets.noone')),
                        TextEntry::make('server.name')
                            ->label(trans('helpdesk::tickets.server'))
                            ->icon('tabler-brand-docker')
                            ->placeholder(trans('helpdesk::tickets.no_server'))
                            ->url(fn (Ticket $ticket) => $ticket->server && auth()->user()->can('update server', $ticket->server) ? EditServer::getUrl(['record' => $ticket->server]) : null),
                        TextEntry::make('author.username')
                            ->label(trans('helpdesk::tickets.created_by'))
                            ->icon('tabler-user')
                            ->placeholder(trans('helpdesk::tickets.unknown'))
                            ->url(fn (Ticket $ticket) => $ticket->author && auth()->user()->can('update user', $ticket->author) ? EditUser::getUrl(['record' => $ticket->author]) : null),
                        TextEntry::make('created_at')
                            ->label(trans('helpdesk::tickets.created_at'))
                            ->since(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC'))
                            ->dateTimeTooltip(timezone: auth()->user()->timezone ?? config('app.timezone', 'UTC')),
                    ]),
                Section::make(trans('helpdesk::tickets.description'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->markdown()
                            ->placeholder(trans('helpdesk::tickets.no_description')),
                        TextEntry::make('opening_attachments')
                            ->label(trans('helpdesk::tickets.attachments'))
                            ->state(fn (Ticket $ticket) => AttachmentList::render($ticket->attachments->whereNull('ticket_message_id')->values()))
                            ->html()
                            ->hidden(fn (Ticket $ticket) => $ticket->attachments->whereNull('ticket_message_id')->isEmpty()),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }
}
